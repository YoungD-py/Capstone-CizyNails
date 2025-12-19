<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index()
    {
        $totalBookings = Booking::count();
        $totalCustomers = User::where('role', 'customer')->count();
        $totalServices = Service::count();
        $todayBookings = Booking::where('booking_date', now()->toDateString())->count();
        
        $recentBookings = Booking::with(['user', 'service'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('totalBookings', 'totalCustomers', 'totalServices', 'todayBookings', 'recentBookings'));
    }

    public function bookings(Request $request)
    {
        $query = Booking::with(['user', 'service']);

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('payment_status') && $request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('date') && $request->date) {
            $query->where('booking_date', $request->date);
        }

        $bookings = $query->orderBy('booking_date')->orderBy('booking_time')->paginate(20);

        return view('admin.bookings', compact('bookings'));
    }

    public function verifyPayment(Request $request, Booking $booking)
    {
        if ($booking->payment_status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Payment already processed'], 400);
        }

        $booking->update([
            'payment_status' => 'verified',
            'payment_verified_at' => now(),
            'status' => 'confirmed',
        ]);

        return response()->json(['success' => true, 'message' => 'Payment verified successfully']);
    }

    public function rejectPayment(Request $request, Booking $booking)
    {
        if ($booking->payment_status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Payment already processed'], 400);
        }

        $booking->update([
            'payment_status' => 'rejected',
            'status' => 'cancelled',
        ]);

        return response()->json(['success' => true, 'message' => 'Payment rejected']);
    }

    public function destroy(Booking $booking)
    {
        // rollback capacity if booking was active (not cancelled)
        if ($booking->status !== 'cancelled') {
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
        }

        $booking->delete();

        return response()->json(['success' => true, 'message' => 'Booking deleted']);
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:bookings,id',
        ]);

        $bookings = Booking::whereIn('id', $validated['ids'])->with(['service'])->get();

        foreach ($bookings as $booking) {
            if ($booking->status !== 'cancelled') {
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
            }

            $booking->delete();
        }

        return response()->json(['success' => true, 'message' => 'Selected bookings deleted']);
    }

    public function services()
    {
        $services = Service::all();
        return view('admin.services', compact('services'));
    }

    public function schedules()
    {
        // Get schedules for next 7 days
        $startDate = now()->toDateString();
        $endDate = now()->addDays(7)->toDateString();
        
        $schedules = \App\Models\Schedule::whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('time_slot')
            ->get()
            ->groupBy('date');

        return view('admin.schedules', compact('schedules'));
    }

    public function customers()
    {
        $customers = User::where('role', 'customer')->paginate(20);
        return view('admin.customers', compact('customers'));
    }
}
