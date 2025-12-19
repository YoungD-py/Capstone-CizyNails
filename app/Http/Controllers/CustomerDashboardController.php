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
                        'payment_status' => 'cancelled',
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
        $services = \App\Models\Service::all();
        
        return view('customer.booking', compact('date', 'services'));
    }
}
