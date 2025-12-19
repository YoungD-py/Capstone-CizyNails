<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class NailArtistDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('nail_artist');
    }

    public function index()
    {
        $todayBookings = Booking::where('booking_date', now()->toDateString())->count();
        $pendingBookings = Booking::where('status', 'pending')->count();
        $completedToday = Booking::where('booking_date', now()->toDateString())
            ->where('status', 'completed')
            ->count();
        
        $upcomingBookings = Booking::with(['user', 'service'])
            ->where('booking_date', '>=', now()->toDateString())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('booking_date')
            ->orderBy('booking_time')
            ->limit(10)
            ->get();

        return view('nail-artist.dashboard', compact(
            'todayBookings', 
            'pendingBookings', 
            'completedToday',
            'upcomingBookings'
        ));
    }

    public function bookings(Request $request)
    {
        $query = Booking::with(['user', 'service']);

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('date') && $request->date) {
            $query->where('booking_date', $request->date);
        }

        $bookings = $query->orderBy('booking_date')
            ->orderBy('booking_time')
            ->paginate(20);

        return view('nail-artist.bookings', compact('bookings'));
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled'
        ]);

        $booking->update([
            'status' => $validated['status']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking status updated successfully'
        ]);
    }
}
