<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Http\Requests\StoreBookingRequest;
use App\Mail\BookingPaymentNotification;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

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

            // Check capacity based on TYPE staff_count with duration consideration
            if ($service->type_id) {
                // Load Type model directly to avoid conflict with 'type' column
                $typeModel = \App\Models\Type::find($service->type_id);
                if ($typeModel) {
                    $typeStaffCount = $typeModel->staff_count;
                
                    // Get all bookings with this TYPE on this date
                    $bookingsWithSameType = Booking::whereHas('service', function ($query) use ($service) {
                        $query->where('type_id', $service->type_id);
                    })
                    ->where('booking_date', $validated['booking_date'])
                    ->where('status', '!=', 'cancelled')
                    ->get();

                    // Convert booking time to minutes
                    list($bookHour, $bookMinute) = explode(':', $validated['booking_time']);
                    $bookTimeInMinutes = $bookHour * 60 + $bookMinute;
                    
                    // Calculate total duration for the new booking with removal consideration
                    $newBookingTotalDuration = $service->duration_minutes;
                    if ($validated['needs_removal'] ?? false) {
                        $newBookingTotalDuration += 30;
                    }
                    $newBookingEndTimeInMinutes = $bookTimeInMinutes + $newBookingTotalDuration;

                    // Count how many bookings overlap with this time considering their duration
                    $overlappingCount = 0;
                    foreach ($bookingsWithSameType as $booking) {
                        // Use total_duration_minutes if available, otherwise fall back to service duration
                        $duration = $booking->total_duration_minutes ?? ($booking->service->duration_minutes ?? 60);

                        // Convert existing booking time to minutes
                        list($existingHour, $existingMinute) = explode(':', $booking->booking_time);
                        $existingTimeInMinutes = $existingHour * 60 + $existingMinute;
                        $bookingEndTimeInMinutes = $existingTimeInMinutes + $duration;

                        // Check if new booking time overlaps with existing booking
                        if ($existingTimeInMinutes <= $bookTimeInMinutes && $bookTimeInMinutes < $bookingEndTimeInMinutes) {
                            $overlappingCount++;
                        }
                    }

                    // Check if we exceed staff capacity
                    if ($overlappingCount >= $typeStaffCount) {
                        return response()->json([
                            'message' => 'This time slot is fully booked. Maximum capacity (' . $typeStaffCount . ' staff) has been reached for this service type.',
                            'errors' => ['booking_time' => ['This time slot is fully booked for this type.']]
                        ], 422);
                    }
                }
            } else {
                // Fallback for legacy services without type_id
                $bookedService = \App\Models\Service::find($validated['service_id']);
                $duration = $bookedService->duration_minutes ?? 60;

                // Get all bookings for this service on this date
                $bookingsForService = Booking::where('service_id', $validated['service_id'])
                    ->where('booking_date', $validated['booking_date'])
                    ->whereIn('status', ['pending', 'confirmed', 'completed'])
                    ->get();

                // Convert booking time to minutes
                list($bookHour, $bookMinute) = explode(':', $validated['booking_time']);
                $bookTimeInMinutes = $bookHour * 60 + $bookMinute;
                
                // Calculate total duration for the new booking with removal consideration
                $newBookingTotalDuration = $duration;
                if ($validated['needs_removal'] ?? false) {
                    $newBookingTotalDuration += 30;
                }

                // Check if any booking overlaps with this slot considering duration
                foreach ($bookingsForService as $booking) {
                    // Use total_duration_minutes if available, otherwise fall back to service duration
                    $existingDuration = $booking->total_duration_minutes ?? $duration;
                    
                    list($existingHour, $existingMinute) = explode(':', $booking->booking_time);
                    $existingTimeInMinutes = $existingHour * 60 + $existingMinute;
                    $bookingEndTimeInMinutes = $existingTimeInMinutes + $existingDuration;

                    if ($existingTimeInMinutes <= $bookTimeInMinutes && $bookTimeInMinutes < $bookingEndTimeInMinutes) {
                        return response()->json([
                            'message' => 'This time slot is already booked.',
                            'errors' => ['booking_time' => ['This time slot is already booked.']]
                        ], 422);
                    }
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
        $user = $request->user();
        
        \Log::info('=== CANCEL BOOKING ATTEMPT ===', [
            'booking_id' => $booking->id,
            'booking_user_id' => $booking->user_id,
            'request_user' => $user,
            'request_user_id' => $user?->id,
            'current_status' => $booking->status,
        ]);

        // AUTH CHECK - sama seperti reschedule
        if (!$user) {
            \Log::error('Cancel: User not authenticated');
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // AUTHORIZATION CHECK - cast to int untuk compare
        $bookingUserId = (int)$booking->user_id;
        $requestUserId = (int)$user->id;
        $userRole = $user->role ?? 'customer';

        if ($bookingUserId !== $requestUserId && $userRole !== 'admin') {
            \Log::warning('Cancel unauthorized - user mismatch', [
                'booking_id' => $booking->id,
                'booking_user_id' => $bookingUserId,
                'request_user_id' => $requestUserId,
                'user_role' => $userRole,
            ]);
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->status === 'cancelled') {
            \Log::info('Booking already cancelled', ['booking_id' => $booking->id]);
            return response()->json(['message' => 'Booking is already cancelled'], 400);
        }

        // Check if booking is in the past
        if ($booking->booking_date < now()->toDateString()) {
            \Log::info('Cannot cancel past booking', ['booking_id' => $booking->id, 'booking_date' => $booking->booking_date]);
            return response()->json(['message' => 'Cannot cancel past bookings'], 400);
        }

        try {
            $schedule = \App\Models\Schedule::where('date', $booking->booking_date)
                ->where('time_slot', $booking->booking_time)
                ->first();

            if ($schedule) {
                $this->decrementScheduleBooking($schedule, $booking->service);
            }

            $booking->update(['status' => 'cancelled']);
            $booking->refresh();

            \Log::info('Booking cancelled successfully', [
                'booking_id' => $booking->id,
                'new_status' => $booking->status,
            ]);

            return response()->json([
                'message' => 'Appointment cancelled successfully',
                'booking' => $booking,
            ]);
        } catch (\Exception $e) {
            \Log::error('Cancel booking error: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Error cancelling booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function retryPayment(Booking $booking, Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized - Not authenticated'], 403);
        }

        if ((int)$booking->user_id !== (int)$user->id) {
            return response()->json(['message' => 'Unauthorized - Not your booking'], 403);
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
        // Get user and booking info for debugging
        $user = $request->user();
        
        // Explicit casting to ensure proper comparison
        $bookingUserId = (int) $booking->user_id;
        $requestUserId = (int) $user->id;
        $userRole = (string) $user->role;
        
        $isOwner = $bookingUserId === $requestUserId;
        $isAdmin = $userRole === 'admin';
        
        \Log::debug('Booking access attempt', [
            'booking_id' => $booking->id,
            'booking_user_id' => $bookingUserId,
            'booking_user_id_type' => gettype($booking->user_id),
            'request_user_id' => $requestUserId,
            'request_user_id_type' => gettype($user->id),
            'user_role' => $userRole,
            'role_type' => gettype($user->role),
            'is_owner' => $isOwner,
            'is_admin' => $isAdmin,
            'allowed' => $isOwner || $isAdmin,
        ]);
        
        // Check authorization
        if (!$isOwner && !$isAdmin) {
            return response()->json([
                'message' => 'Unauthorized. You can only view your own bookings.',
                'booking_id' => $booking->id,
                'your_user_id' => $requestUserId,
                'booking_user_id' => $bookingUserId,
                'your_role' => $userRole,
                'is_owner' => $isOwner,
                'is_admin' => $isAdmin,
            ], 403);
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
                'needs_removal' => 'nullable|in:0,1',
            ]);

            $service = \App\Models\Service::with('type')->find($validated['service_id']);
            $date = $validated['date'];
            $needsRemoval = $validated['needs_removal'] ?? 0;
            
            // Calculate total duration considering removal
            $totalDuration = $service->duration_minutes;
            if ($needsRemoval) {
                $totalDuration += 30;
            }

            // Ensure schedules are generated for this date
            $this->ensureDailySchedules($date);

            $schedules = \App\Models\Schedule::where('date', $date)
                ->orderBy('time_slot')
                ->get();

            \Log::info('Available times check', [
                'service_id' => $validated['service_id'],
                'date' => $date,
                'needs_removal' => $needsRemoval,
                'total_duration' => $totalDuration,
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

                // Check availability based on TYPE staff_count with duration consideration
                $isAvailable = true;
                if ($service->type_id) {
                    // Load Type model directly to avoid conflict with 'type' column
                    $typeModel = \App\Models\Type::find($service->type_id);
                    if ($typeModel) {
                        $typeStaffCount = $typeModel->staff_count;
                        
                        // Get all bookings with this TYPE on this date
                        $bookingsWithSameType = Booking::whereHas('service', function ($query) use ($service) {
                            $query->where('type_id', $service->type_id);
                        })
                        ->where('booking_date', $date)
                        ->where('status', '!=', 'cancelled')
                        ->get();

                        // Convert current time slot to minutes for comparison
                        list($slotHour, $slotMinute) = explode(':', $formattedTime);
                        $slotTimeInMinutes = $slotHour * 60 + $slotMinute;
                        
                        // Calculate when the new booking would end with total duration
                        $slotEndTimeInMinutes = $slotTimeInMinutes + $totalDuration;

                        // Count how many bookings overlap with this time slot considering their duration
                        $overlappingCount = 0;
                        foreach ($bookingsWithSameType as $booking) {
                            // Get the booked service's duration
                            $bookedService = \App\Models\Service::find($booking->service_id);
                            $bookedDuration = $booking->total_duration_minutes ?? ($bookedService->duration_minutes ?? 60); // Use total_duration_minutes if available

                            // Convert booking time to minutes
                            list($bookHour, $bookMinute) = explode(':', $booking->booking_time);
                            $bookTimeInMinutes = $bookHour * 60 + $bookMinute;
                            
                            // Calculate when this booking ends
                            $bookingEndTimeInMinutes = $bookTimeInMinutes + $bookedDuration;

                            // Check if current slot overlaps with this booking
                            // Overlap occurs if: booking_start <= slot_time < booking_end
                            if ($bookTimeInMinutes <= $slotTimeInMinutes && $slotTimeInMinutes < $bookingEndTimeInMinutes) {
                                $overlappingCount++;
                            }
                        }

                        $isAvailable = $overlappingCount < $typeStaffCount;
                        
                        \Log::info('Time slot availability with duration', [
                            'time' => $formattedTime,
                            'type_id' => $service->type_id,
                            'staff_count' => $typeStaffCount,
                            'total_duration' => $totalDuration,
                            'overlapping_bookings' => $overlappingCount,
                            'is_available' => $isAvailable
                        ]);
                    }
                } else {
                    // Fallback for legacy services
                    $bookedService = \App\Models\Service::find($validated['service_id']);
                    $bookedDuration = $bookedService->duration_minutes ?? 60;

                    // Get all bookings for this service on this date
                    $bookingsForService = Booking::where('service_id', $validated['service_id'])
                        ->where('booking_date', $date)
                        ->whereIn('status', ['pending', 'confirmed', 'completed'])
                        ->get();

                    // Convert current time slot to minutes
                    list($slotHour, $slotMinute) = explode(':', $formattedTime);
                    $slotTimeInMinutes = $slotHour * 60 + $slotMinute;
                    
                    // Calculate when the new booking would end with total duration
                    $slotEndTimeInMinutes = $slotTimeInMinutes + $totalDuration;

                    // Check if any booking overlaps with this slot
                    $overlapsWithExistingBooking = false;
                    foreach ($bookingsForService as $booking) {
                        $existingDuration = $booking->total_duration_minutes ?? $bookedDuration;
                        list($bookHour, $bookMinute) = explode(':', $booking->booking_time);
                        $bookTimeInMinutes = $bookHour * 60 + $bookMinute;
                        $bookingEndTimeInMinutes = $bookTimeInMinutes + $existingDuration;

                        if ($bookTimeInMinutes <= $slotTimeInMinutes && $slotTimeInMinutes < $bookingEndTimeInMinutes) {
                            $overlapsWithExistingBooking = true;
                            break;
                        }
                    }
                    
                    $isAvailable = !$overlapsWithExistingBooking;
                    
                    \Log::info('Time slot availability legacy with duration', [
                        'time' => $formattedTime,
                        'service_id' => $validated['service_id'],
                        'total_duration_minutes' => $totalDuration,
                        'overlaps' => $overlapsWithExistingBooking,
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
            // ===== COMPREHENSIVE AUTH DEBUGGING =====
            \Log::info('');
            \Log::info('=============== RESCHEDULE ATTEMPT ===============');
            
            // 1. Request and Session info
            \Log::info('1. Request & Session Details:', [
                'request_path' => $request->path(),
                'request_method' => $request->method(),
                'session_id' => session()->getId(),
                'session_driver' => config('session.driver'),
                'session_lifetime' => config('session.lifetime'),
                'session_cookie_name' => config('session.cookie'),
            ]);

            // 2. Booking info
            \Log::info('2. Booking Details:', [
                'booking_id' => $booking->id,
                'booking_user_id' => $booking->user_id,
                'booking_user_id_type' => gettype($booking->user_id),
                'booking_status' => $booking->status,
            ]);

            // 3. Auth guard and user
            $user = $request->user();
            \Log::info('3. Authentication State:', [
                'request_user_is_null' => $user === null,
                'request_user_id' => $user?->id,
                'request_user_id_type' => $user ? gettype($user->id) : 'null',
                'request_user_name' => $user?->name,
                'current_auth_guard' => \Illuminate\Support\Facades\Auth::getDefaultDriver(),
                'auth_check' => \Illuminate\Support\Facades\Auth::check(),
            ]);

            // 4. Cookie info
            $sessionCookie = config('session.cookie');
            \Log::info('4. Cookie & Session Details:', [
                'session_cookie_name' => $sessionCookie,
                'has_session_cookie' => $request->hasCookie($sessionCookie),
                'session_cookie_value' => $request->cookie($sessionCookie) ? 'EXISTS' : 'MISSING',
                'all_cookies_keys' => array_keys($request->cookies->all()),
                'request_all_session_data' => session()->all(),
            ]);

            // 5. Guard attempts
            $webGuardUser = \Illuminate\Support\Facades\Auth::guard('web')->user();
            \Log::info('5. Direct Guard Check:', [
                'web_guard_user_exists' => $webGuardUser !== null,
                'web_guard_user_id' => $webGuardUser?->id,
                'web_guard_user_name' => $webGuardUser?->name,
            ]);

            // ===== AUTH CHECK =====
            if (!$user) {
                \Log::error('RESCHEDULE FAILED: request->user() is null (Unauthenticated)');
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            // ===== AUTHORIZATION CHECK =====
            $bookingUserId = (int)$booking->user_id;
            $requestUserId = (int)$user->id;
            
            \Log::info('6. User ID Comparison:', [
                'booking_user_id_int' => $bookingUserId,
                'request_user_id_int' => $requestUserId,
                'ids_match' => $bookingUserId === $requestUserId,
            ]);

            if ($bookingUserId !== $requestUserId) {
                \Log::error('RESCHEDULE FAILED: User ID mismatch (Unauthorized)', [
                    'booking_user_id' => $bookingUserId,
                    'request_user_id' => $requestUserId,
                    'booking_owner_id' => $booking->user?->id,
                    'request_user_name' => $user->name,
                ]);
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            \Log::info('✓ AUTH PASSED - User is authenticated and authorized');

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
                
                    // Get all bookings with this TYPE on new date (excluding current booking)
                    $bookingsWithSameType = Booking::whereHas('service', function ($query) use ($service) {
                        $query->where('type_id', $service->type_id);
                    })
                    ->where('booking_date', $validated['booking_date'])
                    ->where('id', '!=', $booking->id)
                    ->where('status', '!=', 'cancelled')
                    ->get();

                    // Convert new booking time to minutes
                    list($newBookHour, $newBookMinute) = explode(':', $validated['booking_time']);
                    $newBookTimeInMinutes = $newBookHour * 60 + $newBookMinute;
                    
                    // Use the current booking's total_duration_minutes for reschedule
                    $reschedulingBookingDuration = $booking->total_duration_minutes ?? ($service->duration_minutes ?? 60);
                    $newBookingEndTimeInMinutes = $newBookTimeInMinutes + $reschedulingBookingDuration;

                    // Count how many bookings overlap with new time considering their duration
                    $overlappingCount = 0;
                    foreach ($bookingsWithSameType as $existingBooking) {
                        // Use total_duration_minutes if available, otherwise fall back to service duration
                        $duration = $existingBooking->total_duration_minutes ?? ($existingBooking->service->duration_minutes ?? 60);

                        // Convert existing booking time to minutes
                        list($existingHour, $existingMinute) = explode(':', $existingBooking->booking_time);
                        $existingTimeInMinutes = $existingHour * 60 + $existingMinute;
                        $bookingEndTimeInMinutes = $existingTimeInMinutes + $duration;

                        // Check if new booking time overlaps with existing booking
                        if ($existingTimeInMinutes <= $newBookTimeInMinutes && $newBookTimeInMinutes < $bookingEndTimeInMinutes) {
                            $overlappingCount++;
                        }
                    }

                    if ($overlappingCount >= $typeStaffCount) {
                        return response()->json([
                            'message' => 'Slot waktu ini sudah penuh. Kapasitas maksimal (' . $typeStaffCount . ' staff) sudah tercapai untuk tipe layanan ini.',
                            'errors' => ['booking_time' => ['Slot waktu penuh']]
                        ], 422);
                    }
                }
            } else {
                // Fallback for legacy services
                $bookedService = $service;
                $serviceDuration = $bookedService->duration_minutes ?? 60;

                // Get all bookings for this service on the new date (excluding the current booking)
                $bookingsForService = Booking::where('service_id', $service->id)
                    ->where('booking_date', $validated['booking_date'])
                    ->where('id', '!=', $booking->id)
                    ->whereIn('status', ['pending', 'confirmed', 'completed'])
                    ->get();

                // Convert new booking time to minutes
                list($newBookHour, $newBookMinute) = explode(':', $validated['booking_time']);
                $newBookTimeInMinutes = $newBookHour * 60 + $newBookMinute;
                
                // Use the current booking's total_duration_minutes for reschedule
                $reschedulingBookingDuration = $booking->total_duration_minutes ?? $serviceDuration;

                // Check if any booking overlaps with new slot considering duration
                foreach ($bookingsForService as $existingBooking) {
                    // Use total_duration_minutes if available, otherwise fall back to service duration
                    $existingDuration = $existingBooking->total_duration_minutes ?? $serviceDuration;
                    
                    list($existingHour, $existingMinute) = explode(':', $existingBooking->booking_time);
                    $existingTimeInMinutes = $existingHour * 60 + $existingMinute;
                    $bookingEndTimeInMinutes = $existingTimeInMinutes + $existingDuration;

                    if ($existingTimeInMinutes <= $newBookTimeInMinutes && $newBookTimeInMinutes < $bookingEndTimeInMinutes) {
                        return response()->json([
                            'message' => 'Slot waktu ini sudah penuh untuk layanan tersebut.',
                            'errors' => ['booking_time' => ['Slot waktu penuh']]
                        ], 422);
                    }
                }
            }

            // Release old schedule slot
            if ($oldSchedule) {
                $this->decrementScheduleBooking($oldSchedule, $service);
            }

            // Book new schedule slot
            if ($newSchedule) {
                $this->incrementScheduleBooking($newSchedule, $service);
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

    /**
     * Helper method to update schedule booking counts (both legacy and new system)
     */
    private function incrementScheduleBooking($schedule, $service)
    {
        if (!$schedule) return;

        // Update new type-based system
        if ($service->type_id) {
            $schedule->incrementTypeBooking($service->type_id);
        }

        // Update legacy columns for backward compatibility
        if (!$service->type_id && $service->getAttribute('type')) {
            if ($service->getAttribute('type') === 'nails_art') {
                $schedule->increment('nails_art_booked');
            } elseif ($service->getAttribute('type') === 'eyelash') {
                $schedule->increment('eyelash_booked');
            }
        }
    }

    /**
     * Helper method to decrement schedule booking counts (both legacy and new system)
     */
    private function decrementScheduleBooking($schedule, $service)
    {
        if (!$schedule) return;

        // Update new type-based system
        if ($service->type_id) {
            $schedule->decrementTypeBooking($service->type_id);
        }

        // Update legacy columns for backward compatibility
        if (!$service->type_id && $service->getAttribute('type')) {
            if ($service->getAttribute('type') === 'nails_art') {
                $schedule->decrement('nails_art_booked');
            } elseif ($service->getAttribute('type') === 'eyelash') {
                $schedule->decrement('eyelash_booked');
            }
        }
    }
}

