<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MidtransService;

class CustomerDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        
        // Jika user tidak ada di session, redirect ke login
        if (!$user) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        // Sync pending payments to avoid stale "pending" after successful payment
        $pendingBookings = $user->bookings()
            ->where('payment_status', 'pending')
            ->whereNotNull('transaction_id')
            ->get();

        if ($pendingBookings->isNotEmpty()) {
            $midtrans = new MidtransService();
            foreach ($pendingBookings as $pending) {
                $statusResult = $midtrans->getTransactionStatus($pending->transaction_id);
                if (!$statusResult['success']) {
                    continue;
                }

                $mtStatus = $statusResult['status']->transaction_status ?? null;
                $fraud = $statusResult['status']->fraud_status ?? null;

                if (in_array($mtStatus, ['capture', 'settlement']) && ($fraud === 'accept' || $fraud === null)) {
                    $pending->update([
                        'payment_status' => 'paid',
                        'status' => 'confirmed',
                    ]);
                } elseif (in_array($mtStatus, ['deny', 'expire', 'cancel'])) {
                    $pending->update([
                        'payment_status' => 'unpaid',
                        'status' => 'cancelled',
                    ]);
                }
            }
        }

        $upcomingBookings = $user->bookings()
            ->where('booking_date', '>=', now()->toDateString())
            ->orderBy('booking_date')
            ->orderBy('booking_time')
            ->get()
            ->load('service');
        
        $pastBookings = $user->bookings()
            ->where('booking_date', '<', now()->toDateString())
            ->orderBy('booking_date', 'desc')
            ->get()
            ->load('service');

        return view('customer.dashboard', compact('user', 'upcomingBookings', 'pastBookings'));
    }

    public function bookingForm(Request $request)
    {
        $date = $request->query('date');
        $services = \App\Models\Service::where('is_active', true)->get();
        
        return view('customer.booking', compact('date', 'services'));
    }

    public function cancelBooking(Request $request, $id)
    {
        $booking = \App\Models\Booking::findOrFail($id);
        
        // Check if user owns this booking
        if ($booking->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action'
            ], 403);
        }
        
        // Check if booking can be cancelled (not already cancelled or completed)
        if (in_array($booking->status, ['cancelled', 'completed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Booking cannot be cancelled'
            ], 400);
        }
        
        // Cancel booking - NO REFUND (payment status remains as is)
        $booking->update([
            'status' => 'cancelled'
        ]);
        
        // Rollback schedule capacity
        $schedule = \App\Models\Schedule::where('date', $booking->booking_date)
            ->where('time_slot', $booking->booking_time)
            ->first();
        
        if ($schedule && $booking->service) {
            if ($booking->service->type === 'nails_art') {
                $schedule->decrement('nails_art_booked');
            } else {
                $schedule->decrement('eyelash_booked');
            }
        }
        
        \Log::info('Customer cancelled booking', [
            'booking_id' => $booking->id,
            'user_id' => $request->user()->id,
            'payment_status' => $booking->payment_status
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully. Note: No refund will be processed.'
        ]);
    }
}
