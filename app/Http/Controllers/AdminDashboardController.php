<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use App\Models\Notification;
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
        $totalRevenue = Booking::where('payment_status', 'paid')->sum('price');
        
        // Handle notifications gracefully if table doesn't exist yet
        $unreadNotifications = 0;
        $recentNotifications = collect();
        
        try {
            $unreadNotifications = Notification::unread()->count();
            $recentNotifications = Notification::with('booking.user')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            // Table doesn't exist yet - log it but don't crash
            \Log::warning('Notifications table not found. Run /public/migrate.php');
        }
        
        $recentBookings = Booking::with(['user', 'service'])
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return view('admin.dashboard', compact('totalBookings', 'totalCustomers', 'totalServices', 'todayBookings', 'totalRevenue', 'unreadNotifications', 'recentNotifications', 'recentBookings'));
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

        if ($request->has('service_id') && $request->service_id) {
            $query->where('service_id', $request->service_id);
        }

        // Sorting
        $sort = $request->get('sort', 'latest');
        if ($sort === 'oldest') {
            $query->orderBy('booking_date', 'asc')->orderBy('booking_time', 'asc');
        } else {
            $query->orderBy('booking_date', 'desc')->orderBy('booking_time', 'desc');
        }

        $bookings = $query->paginate(5);
        $services = \App\Models\Service::where('is_active', true)->orderBy('name')->get();

        return view('admin.bookings', compact('bookings', 'services'));
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
            $this->decrementScheduleBooking($schedule, $booking->service);
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

            if ($schedule && $booking->service) {
                $this->decrementScheduleBooking($schedule, $booking->service);
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

                if ($schedule && $booking->service) {
                    $this->decrementScheduleBooking($schedule, $booking->service);
                }
            }

            $booking->delete();
        }

        return response()->json(['success' => true, 'message' => 'Selected bookings deleted']);
    }

    public function services()
    {
        $services = Service::with('type', 'subtype')->paginate(5);
        $types = \App\Models\Type::with('subtypes')->get();
        return view('admin.services', compact('services', 'types'));
    }

    private function getSubtypesForType($typeId)
    {
        if (!$typeId) return [];
        return \App\Models\Subtype::where('type_id', $typeId)->get();
    }

    public function storeService(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:services,name',
            'description' => 'nullable|string|max:1000',
               'type_id' => 'nullable|exists:types,id',
               'subtype_id' => 'nullable|exists:subtypes,id',
               'type' => 'nullable|in:nails_art,eyelash,other',
               'subtype' => 'nullable|string|in:natural,extension',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'price' => 'required|numeric|min:0|max:99999999.99',
            'is_active' => 'nullable|boolean',
            'enable_removal' => 'nullable|in:0,1',
            'image' => 'nullable|image|max:2048',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['enable_removal'] = $request->input('enable_removal', 0);

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
            'type_id' => 'nullable|exists:types,id',
            'subtype_id' => 'nullable|exists:subtypes,id',
            // legacy fallback
            'type' => 'nullable|in:nails_art,eyelash,other',
            'subtype' => 'nullable|string|in:natural,extension',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'price' => 'required|numeric|min:0|max:99999999.99',
            'is_active' => 'nullable|boolean',
            'enable_removal' => 'nullable|in:0,1',
            'image' => 'nullable|image|max:2048',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['enable_removal'] = $request->input('enable_removal', 0);

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
        // Hapus semua booking yang terkait dengan service ini
        $bookingCount = $service->bookings()->count();
        
        if ($bookingCount > 0) {
            // Log informasi tentang booking yang akan dihapus
            \Log::info('Deleting service with associated bookings', [
                'service_id' => $service->id,
                'service_name' => $service->name,
                'booking_count' => $bookingCount
            ]);
            
            // Hapus semua booking terkait service ini
            $service->bookings()->delete();
        }

        $service->delete();

        return redirect()->route('admin.services')->with('success', 'Service deleted successfully! (' . $bookingCount . ' associated bookings were also deleted)');
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

        // Get all types with their staff counts
        $types = \App\Models\Type::orderBy('name')->get();

        return view('admin.schedules', compact('schedules', 'types'));
    }

    public function customers()
    {
        $customers = User::where('role', 'customer')->with('bookings')->paginate(5);
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
            \Log::info('Admin creating booking', ['request_data' => $request->all()]);

            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|max:20',
                'service_id' => 'required|exists:services,id',
                'booking_date' => 'required|date|after_or_equal:today',
                'booking_time' => 'required|date_format:H:i',
                'notes' => 'nullable|string|max:500',
            ]);

            \Log::info('Validation passed', ['validated_data' => $validated]);

            // Check if the booking date and time has already passed
            $bookingDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $validated['booking_date'] . ' ' . $validated['booking_time']);
            if ($bookingDateTime->isPast()) {
                return response()->json([
                    'message' => 'Cannot create booking for past date/time',
                    'errors' => ['booking_time' => ['Selected time has already passed']]
                ], 422);
            }

            $service = Service::with('type')->findOrFail($validated['service_id']);

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

            // UPDATED LOGIC: Check staff availability based on TYPE staff_count with duration consideration
            // Jika service memiliki type_id, hitung total booking dengan TYPE yang sama dengan mempertimbangkan durasi
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

                    // Count how many bookings overlap with this time considering their duration
                    $overlappingCount = 0;
                    foreach ($bookingsWithSameType as $booking) {
                        $bookedService = \App\Models\Service::find($booking->service_id);
                        $duration = $bookedService->duration_minutes ?? 60;

                        // Convert existing booking time to minutes
                        list($existingHour, $existingMinute) = explode(':', $booking->booking_time);
                        $existingTimeInMinutes = $existingHour * 60 + $existingMinute;
                        $bookingEndTimeInMinutes = $existingTimeInMinutes + $duration;

                        // Check if new booking time overlaps with existing booking
                        if ($existingTimeInMinutes <= $bookTimeInMinutes && $bookTimeInMinutes < $bookingEndTimeInMinutes) {
                            $overlappingCount++;
                        }
                    }

                    // Cek apakah sudah mencapai atau melebihi staff count
                    if ($overlappingCount >= $typeStaffCount) {
                        return response()->json([
                            'message' => 'Time slot is fully booked. Maximum capacity (' . $typeStaffCount . ' staff) has been reached for this service type.',
                            'errors' => ['booking_time' => ['Time slot is full for this type']]
                        ], 422);
                    }
                }
            } else {
                // FALLBACK: Gunakan logic lama untuk backward compatibility (legacy services)
                $bookedService = \App\Models\Service::find($validated['service_id']);
                $duration = $bookedService->duration_minutes ?? 60;

                // Get all bookings for this service on this date
                $bookingsForService = Booking::where('service_id', $validated['service_id'])
                    ->where('booking_date', $validated['booking_date'])
                    ->where('status', '!=', 'cancelled')
                    ->get();

                // Convert booking time to minutes
                list($bookHour, $bookMinute) = explode(':', $validated['booking_time']);
                $bookTimeInMinutes = $bookHour * 60 + $bookMinute;

                // Check if any booking overlaps with this slot considering duration
                foreach ($bookingsForService as $booking) {
                    list($existingHour, $existingMinute) = explode(':', $booking->booking_time);
                    $existingTimeInMinutes = $existingHour * 60 + $existingMinute;
                    $bookingEndTimeInMinutes = $existingTimeInMinutes + $duration;

                    if ($existingTimeInMinutes <= $bookTimeInMinutes && $bookTimeInMinutes < $bookingEndTimeInMinutes) {
                        return response()->json([
                            'message' => 'Time slot is fully booked',
                            'errors' => ['booking_time' => ['Time slot is full']]
                        ], 422);
                    }
                }
            }

            // Create booking with confirmed and paid status (manual payment received)
            $booking = Booking::create([
                'user_id' => $validated['user_id'],
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'service_id' => $validated['service_id'],
                'booking_date' => $validated['booking_date'],
                'booking_time' => $validated['booking_time'],
                'total_duration_minutes' => $service->duration_minutes,
                'needs_removal' => false,
                'price' => $service->price,
                'notes' => $validated['notes'] ?? null,
                'status' => 'confirmed',  // Auto-confirmed for admin booking
                'payment_status' => 'paid',  // Mark as paid since payment handled manually
                'payment_verified_at' => now(),
                'transaction_id' => 'MANUAL-' . time(),  // Manual transaction ID
            ]);

            // Update schedule capacity
            $this->incrementScheduleBooking($schedule, $service);

            return response()->json([
                'message' => 'Booking created successfully',
                'booking' => $booking->load('user', 'service'),
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Admin booking creation error: ' . $e->getMessage(), [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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

    /**
     * Helper method to increment schedule booking count
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
     * Helper method to decrement schedule booking count
     */
    private function decrementScheduleBooking($schedule, $service)
    {
        if (!$schedule || !$service) return;
        
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

    // Notification methods
    public function getNotifications()
    {
        try {
            $unread = Notification::unread()->count();
            $notifications = Notification::with('booking.user')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            $formattedNotifications = $notifications->map(function($n) {
                try {
                    return [
                        'id' => $n->id,
                        'type' => $n->type,
                        'title' => $n->title,
                        'message' => $n->message,
                        'is_read' => (bool)$n->is_read,
                        'booking_id' => $n->booking_id,
                        'customer' => ($n->booking && $n->booking->user) ? $n->booking->user->name : 'Unknown',
                        'created_at' => $n->created_at ? $n->created_at->diffForHumans() : 'Unknown',
                    ];
                } catch (\Exception $e) {
                    \Log::error('Error formatting notification', ['notification_id' => $n->id, 'error' => $e->getMessage()]);
                    return [
                        'id' => $n->id,
                        'title' => $n->title,
                        'customer' => 'Unknown',
                        'error' => true,
                    ];
                }
            });
            
            return response()->json([
                'unread_count' => $unread,
                'notifications' => $formattedNotifications,
                'success' => true,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getNotifications', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'unread_count' => 0,
                'notifications' => [],
                'error' => $e->getMessage(),
                'success' => false,
            ], 500);
        }
    }

    public function markNotificationAsRead($id)
    {
        try {
            $notification = Notification::find($id);
            if ($notification) {
                $notification->markAsRead();
                return response()->json(['success' => true]);
            }
            return response()->json(['success' => false], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 503);
        }
    }

    public function markAllNotificationsAsRead()
    {
        try {
            Notification::unread()->update(['is_read' => true]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 503);
        }
    }

    // Test endpoint to create dummy notification (for debugging)
    public function createTestNotification()
    {
        try {
            $booking = Booking::first();
            if (!$booking) {
                return response()->json(['error' => 'No bookings found'], 404);
            }

            $notification = Notification::create([
                'booking_id' => $booking->id,
                'type' => 'payment_confirmed',
                'title' => 'Test Order - ' . $booking->user->name,
                'message' => 'Ini adalah notifikasi test untuk debugging',
                'is_read' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test notification created',
                'notification' => $notification
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
}

