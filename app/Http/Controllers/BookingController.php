<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Http\Requests\StoreBookingRequest;
use App\Mail\BookingPaymentNotification;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{

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

            $schedule = \App\Models\Schedule::where('date', $validated['booking_date'])
                ->where('time_slot', $validated['booking_time'])
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

            return response()->json([
                'message' => 'Booking created successfully',
                'booking' => $booking->load('service'),
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

    public function uploadPaymentProof(Request $request, Booking $booking)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if ($booking->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($request->hasFile('payment_proof')) {
            $file = $request->file('payment_proof');
            $path = $file->store('payment_proofs', 'public');

            $booking->update([
                'payment_proof_path' => $path,
                'payment_status' => 'pending',
            ]);

            Mail::to('deruanggoro009@gmail.com')->send(new BookingPaymentNotification($booking));

            return response()->json([
                'message' => 'Payment proof uploaded successfully',
                'booking' => $booking,
            ], 200);
        }

        return response()->json(['message' => 'No file uploaded'], 400);
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

    public function getAvailableTimes(Request $request)
    {
        try {
            $validated = $request->validate([
                'service_id' => 'required|exists:services,id',
                'date' => 'required|date|after_or_equal:today',
            ]);

            $service = \App\Models\Service::find($validated['service_id']);
            $date = $validated['date'];

            $schedules = \App\Models\Schedule::where('date', $date)->get();

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
