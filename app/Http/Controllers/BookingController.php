<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Http\Requests\StoreBookingRequest;
use App\Mail\BookingPaymentNotification;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    private function ensureDailySchedules(string $date): void
    {
        // Generate default time slots if none exist for the given date
        $existingCount = \App\Models\Schedule::where('date', $date)->count();
        if ($existingCount > 0) {
            return;
        }

        // Business hours: 10:00 - 20:00, interval 60 minutes
        $start = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $date . ' 10:00');
        $end = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $date . ' 20:00');

        $slots = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $slots[] = [
                'date' => $date,
                'time_slot' => $cursor->format('H:i:s'),
                'nails_art_booked' => 0,
                'eyelash_booked' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $cursor->addMinutes(60);
        }

        if (!empty($slots)) {
            // Avoid unique constraint conflicts if parallel requests happen
            foreach ($slots as $slot) {
                \App\Models\Schedule::firstOrCreate(
                    ['date' => $slot['date'], 'time_slot' => $slot['time_slot']],
                    ['nails_art_booked' => 0, 'eyelash_booked' => 0]
                );
            }
        }
    }

    public function store(StoreBookingRequest $request)
    {
        try {
            $validated = $request->validated();

            // Check if the booking date and time has already passed
            $bookingDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $validated['booking_date'] . ' ' . $validated['booking_time']);
            if ($bookingDateTime->isPast()) {
                return response()->json([
                    'message' => 'Tidak dapat melakukan booking pada waktu yang sudah berlalu. Silakan pilih waktu yang lain.',
                    'errors' => ['booking_time' => ['Waktu booking sudah berlalu. Silakan pilih waktu yang masih tersedia.']]
                ], 422);
            }

            $service = \App\Models\Service::with('type')->find($validated['service_id']);
            if (!$service) {
                return response()->json([
                    'message' => 'Service not found',
                    'errors' => ['service_id' => ['Service not found']]
                ], 422);
            }

            // Ensure schedules exist for the selected date
            $this->ensureDailySchedules($validated['booking_date']);

            $schedule = \App\Models\Schedule::where('date', $validated['booking_date'])
                ->where('time_slot', \Carbon\Carbon::createFromFormat('H:i', $validated['booking_time'])->format('H:i:s'))
                ->first();

            if (!$schedule) {
                return response()->json([
                    'message' => 'No available schedule for this service on the selected date.',
                    'errors' => ['booking_date' => ['No available schedule for this service on the selected date.']]
                ], 422);
            }

            // Check capacity based on TYPE staff_count
            if ($service->type_id) {
                // Load Type model directly to avoid conflict with 'type' column
                $typeModel = \App\Models\Type::find($service->type_id);
                if ($typeModel) {
                    $typeStaffCount = $typeModel->staff_count;
                
                    // Count all bookings with this TYPE at this time slot
                    $bookingsWithSameType = Booking::whereHas('service', function ($query) use ($service) {
                        $query->where('type_id', $service->type_id);
                    })
                    ->where('booking_date', $validated['booking_date'])
                    ->where('booking_time', $validated['booking_time'])
                    ->where('status', '!=', 'cancelled')
                    ->count();

                    // Check if we exceed staff capacity
                    if ($bookingsWithSameType >= $typeStaffCount) {
                        return response()->json([
                            'message' => 'This time slot is fully booked. Maximum capacity (' . $typeStaffCount . ' staff) has been reached for this service type.',
                            'errors' => ['booking_time' => ['This time slot is fully booked for this type.']]
                        ], 422);
                    }
                }
            } else {
                // Fallback for legacy services without type_id
                $countBookingsAtTime = Booking::where('service_id', $validated['service_id'])
                    ->where('booking_date', $validated['booking_date'])
                    ->where('booking_time', $validated['booking_time'])
                    ->whereIn('status', ['pending', 'confirmed', 'completed'])
                    ->count();

                if ($countBookingsAtTime >= 1) {
                    return response()->json([
                        'message' => 'This time slot is already booked.',
                        'errors' => ['booking_time' => ['This time slot is already booked.']]
                    ], 422);
                }
            }

            $totalDuration = $service->duration_minutes;
            if ($validated['needs_removal'] ?? false) {
                $totalDuration += 30;
            }

            $booking = $request->user()->bookings()->create([
                'service_id' => $validated['service_id'],
                'booking_date' => $validated['booking_date'],
                'booking_time' => $validated['booking_time'],
                'total_duration_minutes' => $totalDuration,
                'needs_removal' => $validated['needs_removal'] ?? false,
                'price' => $service->price,
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);

            $midtransService = new MidtransService();
            $transactionResult = $midtransService->createTransaction($booking);

            if (!$transactionResult['success']) {
                return response()->json([
                    'message' => 'Failed to create payment transaction',
                    'error' => $transactionResult['error']
                ], 500);
            }

            // Store transaction_id (order_id) for later status checks and webhook reconciliation
            if (!empty($transactionResult['order_id'])) {
                $booking->update(['transaction_id' => $transactionResult['order_id']]);
            }

            return response()->json([
                'message' => 'Booking created successfully',
                'booking' => $booking->load('service'),
                'snap_token' => $transactionResult['snap_token'],
                'order_id' => $transactionResult['order_id'],
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Booking creation error: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => $request->user()->id ?? null,
            ]);

            return response()->json([
                'message' => 'An error occurred while creating the booking. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function cancel(Booking $booking, Request $request)
    {
        if ($booking->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->status === 'cancelled') {
            return response()->json(['message' => 'Booking is already cancelled'], 400);
        }

        // Check if booking is in the past
        if ($booking->booking_date < now()->toDateString()) {
            return response()->json(['message' => 'Cannot cancel past bookings'], 400);
        }

        $schedule = \App\Models\Schedule::where('date', $booking->booking_date)
            ->where('time_slot', $booking->booking_time)
            ->first();

        if ($schedule && !$booking->service->type_id) {
            // Only update schedule for legacy services (yang tidak punya type_id)
            if ($booking->service->getAttribute('type') === 'nails_art') {
                $schedule->decrement('nails_art_booked');
            } else {
                $schedule->decrement('eyelash_booked');
            }
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Booking cancelled successfully',
            'booking' => $booking,
        ]);
    }

    public function retryPayment(Booking $booking, Request $request)
    {
        if ($booking->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->payment_status === 'paid') {
            return response()->json(['message' => 'This booking is already paid'], 400);
        }

        if ($booking->status === 'cancelled') {
            return response()->json(['message' => 'Cannot retry payment for cancelled booking'], 400);
        }

        try {
            $midtransService = new MidtransService();
            $result = $midtransService->getSnapToken($booking);

            if (!$result['success']) {
                return response()->json([
                    'message' => 'Failed to create payment transaction',
                    'error' => $result['error']
                ], 500);
            }

            return response()->json([
                'message' => 'Payment retry initiated',
                'snap_token' => $result['snap_token'],
            ]);
        } catch (\Exception $e) {
            \Log::error('Retry payment error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while retrying payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Booking $booking, Request $request)
    {
        if ($booking->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'booking' => $booking->load('service', 'user'),
        ]);
    }

    public function getAvailableTimes(Request $request)
    {
        try {
            $validated = $request->validate([
                'service_id' => 'required|exists:services,id',
                'date' => 'required|date|after_or_equal:today',
            ]);

            $service = \App\Models\Service::with('type')->find($validated['service_id']);
            $date = $validated['date'];

            // Ensure schedules are generated for this date
            $this->ensureDailySchedules($date);

            $schedules = \App\Models\Schedule::where('date', $date)
                ->orderBy('time_slot')
                ->get();

            \Log::info('Available times check', [
                'service_id' => $validated['service_id'],
                'date' => $date,
                'schedules_count' => $schedules->count(),
                'service_type_id' => $service->type_id
            ]);

            if ($schedules->isEmpty()) {
                \Log::warning('No schedules found for date', ['date' => $date]);
                return response()->json(['times' => []]);
            }

            $times = [];
            $now = \Carbon\Carbon::now();
            
            foreach ($schedules as $schedule) {
                $timeValue = $schedule->time_slot;
                if (is_object($timeValue)) {
                    $formattedTime = $timeValue->format('H:i');
                } else {
                    $formattedTime = \Carbon\Carbon::createFromFormat('H:i:s', $timeValue)->format('H:i');
                }

                // Create datetime for this time slot
                $slotDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $formattedTime);
                
                // Skip time slots that have already passed
                if ($slotDateTime->isPast()) {
                    continue;
                }

                // Check availability based on TYPE staff_count
                $isAvailable = true;
                if ($service->type_id) {
                    // Load Type model directly to avoid conflict with 'type' column
                    $typeModel = \App\Models\Type::find($service->type_id);
                    if ($typeModel) {
                        $typeStaffCount = $typeModel->staff_count;
                        
                        // Count all bookings with this TYPE at this time slot
                        $bookingsWithSameType = Booking::whereHas('service', function ($query) use ($service) {
                            $query->where('type_id', $service->type_id);
                        })
                        ->where('booking_date', $date)
                        ->where('booking_time', $formattedTime)
                        ->where('status', '!=', 'cancelled')
                        ->count();

                        $isAvailable = $bookingsWithSameType < $typeStaffCount;
                        
                        \Log::info('Time slot availability', [
                            'time' => $formattedTime,
                            'type_id' => $service->type_id,
                            'staff_count' => $typeStaffCount,
                            'bookings_count' => $bookingsWithSameType,
                            'is_available' => $isAvailable
                        ]);
                    }
                } else {
                    // Fallback for legacy services
                    $countBookingsAtTime = Booking::where('service_id', $validated['service_id'])
                        ->where('booking_date', $date)
                        ->where('booking_time', $formattedTime)
                        ->whereIn('status', ['pending', 'confirmed', 'completed'])
                        ->count();
                    $isAvailable = $countBookingsAtTime < 1;
                    
                    \Log::info('Time slot availability (legacy)', [
                        'time' => $formattedTime,
                        'service_id' => $validated['service_id'],
                        'bookings_count' => $countBookingsAtTime,
                        'is_available' => $isAvailable
                    ]);
                }

                $times[] = [
                    'time' => $formattedTime,
                    'available' => $isAvailable,
                ];
            }

            return response()->json(['times' => $times]);
        } catch (\Exception $e) {
            \Log::error('Error loading available times: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'service_id' => $validated['service_id'] ?? null,
                'date' => $validated['date'] ?? null
            ]);
            return response()->json(['error' => 'Error loading available times'], 500);
        }
    }

    public function reschedule(Booking $booking, Request $request)
    {
        try {
            // Check authorization
            if ($booking->user_id !== $request->user()->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Check if booking can be rescheduled
            if ($booking->status === 'cancelled') {
                return response()->json([
                    'message' => 'Booking yang sudah dibatalkan tidak dapat dijadwal ulang.',
                    'errors' => ['status' => ['Booking telah dibatalkan']]
                ], 422);
            }

            if ($booking->status === 'completed') {
                return response()->json([
                    'message' => 'Booking yang sudah selesai tidak dapat dijadwal ulang.',
                    'errors' => ['status' => ['Booking telah selesai']]
                ], 422);
            }

            // Check reschedule limit
            if ($booking->reschedule_count >= 1) {
                return response()->json([
                    'message' => 'Anda hanya dapat melakukan reschedule sekali. Batas reschedule sudah tercapai.',
                    'errors' => ['reschedule_count' => ['Batas reschedule sudah tercapai']]
                ], 422);
            }

            $validated = $request->validate([
                'booking_date' => 'required|date|after_or_equal:today|before_or_equal:' . now()->addMonths(3)->toDateString(),
                'booking_time' => 'required|date_format:H:i',
            ]);

            // Check if the new booking date and time has already passed
            $newBookingDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $validated['booking_date'] . ' ' . $validated['booking_time']);
            if ($newBookingDateTime->isPast()) {
                return response()->json([
                    'message' => 'Tidak dapat reschedule ke waktu yang sudah berlalu.',
                    'errors' => ['booking_time' => ['Waktu yang dipilih sudah berlalu']]
                ], 422);
            }

            $service = $booking->service;
            
            // Get old schedule to release the slot
            $oldBookingTime = is_string($booking->booking_time) ? $booking->booking_time : $booking->booking_time->format('H:i:s');
            $oldSchedule = \App\Models\Schedule::where('date', $booking->booking_date)
                ->where('time_slot', $oldBookingTime)
                ->first();

            // Ensure schedules exist for the new date
            $this->ensureDailySchedules($validated['booking_date']);

            // Check new schedule availability
            $newSchedule = \App\Models\Schedule::where('date', $validated['booking_date'])
                ->where('time_slot', \Carbon\Carbon::createFromFormat('H:i', $validated['booking_time'])->format('H:i:s'))
                ->first();

            if (!$newSchedule) {
                return response()->json([
                    'message' => 'Jadwal tidak tersedia untuk tanggal tersebut.',
                    'errors' => ['booking_date' => ['Jadwal tidak tersedia']]
                ], 422);
            }

            // Check capacity for new schedule
            if ($service->type_id) {
                // Load Type model directly to avoid conflict with 'type' column
                $typeModel = \App\Models\Type::find($service->type_id);
                if ($typeModel) {
                    $typeStaffCount = $typeModel->staff_count;
                
                    // Count all bookings with this TYPE at new time slot (excluding current booking)
                    $bookingsWithSameType = Booking::whereHas('service', function ($query) use ($service) {
                        $query->where('type_id', $service->type_id);
                    })
                    ->where('booking_date', $validated['booking_date'])
                    ->where('booking_time', $validated['booking_time'])
                    ->where('id', '!=', $booking->id)
                    ->where('status', '!=', 'cancelled')
                    ->count();

                    if ($bookingsWithSameType >= $typeStaffCount) {
                        return response()->json([
                            'message' => 'Slot waktu ini sudah penuh. Kapasitas maksimal (' . $typeStaffCount . ' staff) sudah tercapai untuk tipe layanan ini.',
                            'errors' => ['booking_time' => ['Slot waktu penuh']]
                        ], 422);
                    }
                }
            } else {
                // Fallback for legacy services
                if ($service->type === 'nails_art' && $newSchedule->nails_art_booked >= 2) {
                    return response()->json([
                        'message' => 'Slot waktu ini sudah penuh untuk layanan Nails Art.',
                        'errors' => ['booking_time' => ['Slot waktu penuh']]
                    ], 422);
                }

                if ($service->type === 'eyelash' && $newSchedule->eyelash_booked >= 1) {
                    return response()->json([
                        'message' => 'Slot waktu ini sudah penuh untuk layanan Eyelash.',
                        'errors' => ['booking_time' => ['Slot waktu penuh']]
                    ], 422);
                }
            }

            // Release old schedule slot (for backward compatibility only)
            if ($oldSchedule && !$service->type_id) {
                if ($service->getAttribute('type') === 'nails_art') {
                    $oldSchedule->decrement('nails_art_booked');
                } else {
                    $oldSchedule->decrement('eyelash_booked');
                }
            }

            // Book new schedule slot (for backward compatibility only)
            if ($newSchedule && !$service->type_id) {
                if ($service->getAttribute('type') === 'nails_art') {
                    $newSchedule->increment('nails_art_booked');
                } else {
                    $newSchedule->increment('eyelash_booked');
                }
            }

            // Update booking
            $booking->update([
                'booking_date' => $validated['booking_date'],
                'booking_time' => $validated['booking_time'],
                'reschedule_count' => $booking->reschedule_count + 1,
            ]);

            return response()->json([
                'message' => 'Booking berhasil dijadwal ulang!',
                'booking' => $booking->fresh()->load('service'),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Reschedule error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat reschedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

