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

            // Check if time slot is available
            $existingBooking = Booking::where('service_id', $validated['service_id'])
                ->where('booking_date', $validated['booking_date'])
                ->where('booking_time', $validated['booking_time'])
                ->where('status', '!=', 'cancelled')
                ->exists();

            if ($existingBooking) {
                return response()->json([
                    'message' => 'This time slot is already booked. Please choose another time.',
                    'errors' => ['booking_time' => ['This time slot is already booked. Please choose another time.']]
                ], 422);
            }

            $service = \App\Models\Service::find($validated['service_id']);
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

            if ($service->type === 'nails_art' && $schedule->nails_art_booked >= 2) {
                return response()->json([
                    'message' => 'This time slot is fully booked for Nails Art.',
                    'errors' => ['booking_time' => ['This time slot is fully booked for Nails Art.']]
                ], 422);
            }

            if ($service->type === 'eyelash' && $schedule->eyelash_booked >= 1) {
                return response()->json([
                    'message' => 'This time slot is fully booked for Eyelash service.',
                    'errors' => ['booking_time' => ['This time slot is fully booked for Eyelash service.']]
                ], 422);
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

            if ($service->type === 'nails_art') {
                $schedule->increment('nails_art_booked');
            } else {
                $schedule->increment('eyelash_booked');
            }

            $midtransService = new MidtransService();
            $transactionResult = $midtransService->createTransaction($booking);

            if (!$transactionResult['success']) {
                return response()->json([
                    'message' => 'Failed to create payment transaction',
                    'error' => $transactionResult['error']
                ], 500);
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

        if ($schedule) {
            if ($booking->service->type === 'nails_art') {
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

            $service = \App\Models\Service::find($validated['service_id']);
            $date = $validated['date'];

            // Ensure schedules are generated for this date
            $this->ensureDailySchedules($date);

            $schedules = \App\Models\Schedule::where('date', $date)
                ->orderBy('time_slot')
                ->get();

            if ($schedules->isEmpty()) {
                return response()->json(['times' => []]);
            }

            $times = [];
            foreach ($schedules as $schedule) {
                $isAvailable = false;

                if ($service->type === 'nails_art') {
                    $isAvailable = $schedule->nails_art_booked < 2;
                } else {
                    $isAvailable = $schedule->eyelash_booked < 1;
                }

                $timeValue = $schedule->time_slot;
                if (is_object($timeValue)) {
                    $formattedTime = $timeValue->format('H:i');
                } else {
                    $formattedTime = \Carbon\Carbon::createFromFormat('H:i:s', $timeValue)->format('H:i');
                }

                $times[] = [
                    'time' => $formattedTime,
                    'available' => $isAvailable,
                ];
            }

            return response()->json(['times' => $times]);
        } catch (\Exception $e) {
            \Log::error('Error loading available times: ' . $e->getMessage());
            return response()->json(['error' => 'Error loading available times'], 500);
        }
    }
}
