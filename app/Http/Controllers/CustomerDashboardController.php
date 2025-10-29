<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CustomerDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = $request->user();
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
