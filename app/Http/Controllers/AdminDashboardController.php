<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    public function cancelBooking(Booking $booking)
    {
        // Check if booking can be cancelled
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

        \Log::info('Admin cancelled booking', [
            'booking_id' => $booking->id,
            'payment_status' => $booking->payment_status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully. No refund processed.'
        ]);
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

    public function storeService(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:services,name',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:nails_art,eyelash,other',
            'subtype' => 'nullable|string|in:natural,extension',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'staff_count' => 'required|integer|min:1|max:10',
            'price' => 'required|numeric|min:0|max:99999999.99',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|max:2048',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('services', 'public');
        }

        $service = Service::create($validated);

        return redirect()->route('admin.services')->with('success', 'Service created successfully!');
    }

    public function updateService(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:services,name,' . $service->id,
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:nails_art,eyelash,other',
            'subtype' => 'nullable|string|in:natural,extension',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'staff_count' => 'required|integer|min:1|max:10',
            'price' => 'required|numeric|min:0|max:99999999.99',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|max:2048',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            $newPath = $request->file('image')->store('services', 'public');
            if ($service->image_path) {
                Storage::disk('public')->delete($service->image_path);
            }
            $validated['image_path'] = $newPath;
        }

        $service->update($validated);

        return redirect()->route('admin.services')->with('success', 'Service updated successfully!');
    }

    public function deleteService(Service $service)
    {
        // Check if service has active bookings
        if ($service->bookings()->where('status', '!=', 'cancelled')->exists()) {
            return back()->with('error', 'Cannot delete service with active bookings!');
        }

        $service->delete();

        return redirect()->route('admin.services')->with('success', 'Service deleted successfully!');
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

    public function getCustomersList()
    {
        $customers = User::where('role', 'customer')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();
        return response()->json($customers);
    }

    public function getServicesList()
    {
        $services = Service::where('is_active', true)
            ->select('id', 'name', 'type', 'duration_minutes', 'price')
            ->get();
        return response()->json($services);
    }

    public function createBooking(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'service_id' => 'required|exists:services,id',
                'booking_date' => 'required|date|after_or_equal:today',
                'booking_time' => 'required|date_format:H:i',
                'notes' => 'nullable|string|max:500',
            ]);

            // Check if the booking date and time has already passed
            $bookingDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $validated['booking_date'] . ' ' . $validated['booking_time']);
            if ($bookingDateTime->isPast()) {
                return response()->json([
                    'message' => 'Cannot create booking for past date/time',
                    'errors' => ['booking_time' => ['Selected time has already passed']]
                ], 422);
            }

            $service = Service::findOrFail($validated['service_id']);

            // Ensure schedules exist for the selected date
            $this->ensureDailySchedules($validated['booking_date']);

            // Check schedule availability
            $schedule = \App\Models\Schedule::where('date', $validated['booking_date'])
                ->where('time_slot', \Carbon\Carbon::createFromFormat('H:i', $validated['booking_time'])->format('H:i:s'))
                ->first();

            if (!$schedule) {
                return response()->json([
                    'message' => 'No schedule available for selected date/time',
                    'errors' => ['booking_date' => ['No schedule available']]
                ], 422);
            }

            // Check capacity
            if ($service->type === 'nails_art' && $schedule->nails_art_booked >= 2) {
                return response()->json([
                    'message' => 'Time slot is fully booked for Nails Art',
                    'errors' => ['booking_time' => ['Time slot is full']]
                ], 422);
            }

            if ($service->type === 'eyelash' && $schedule->eyelash_booked >= 1) {
                return response()->json([
                    'message' => 'Time slot is fully booked for Eyelash',
                    'errors' => ['booking_time' => ['Time slot is full']]
                ], 422);
            }

            // Create booking with confirmed and paid status (manual payment received)
            $booking = Booking::create([
                'user_id' => $validated['user_id'],
                'service_id' => $validated['service_id'],
                'booking_date' => $validated['booking_date'],
                'booking_time' => $validated['booking_time'],
                'total_duration_minutes' => $service->duration_minutes,
                'needs_removal' => false,
                'price' => $service->price,
                'notes' => $validated['notes'] ?? null,
                'status' => 'confirmed',  // Auto-confirmed for admin booking
                'payment_status' => 'verified',  // Mark as verified since payment handled manually
                'payment_verified_at' => now(),
                'transaction_id' => 'MANUAL-' . time(),  // Manual transaction ID
            ]);

            // Update schedule capacity
            if ($service->type === 'nails_art') {
                $schedule->increment('nails_art_booked');
            } else {
                $schedule->increment('eyelash_booked');
            }

            return response()->json([
                'message' => 'Booking created successfully',
                'booking' => $booking->load('user', 'service'),
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Admin booking creation error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while creating the booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function ensureDailySchedules(string $date): void
    {
        $existingCount = \App\Models\Schedule::where('date', $date)->count();
        if ($existingCount > 0) {
            return;
        }

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
            foreach ($slots as $slot) {
                \App\Models\Schedule::firstOrCreate(
                    ['date' => $slot['date'], 'time_slot' => $slot['time_slot']],
                    ['nails_art_booked' => 0, 'eyelash_booked' => 0]
                );
            }
        }
    }
}
