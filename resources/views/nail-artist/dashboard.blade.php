<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nail Artist Dashboard - Cizy Nails</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('img/cizyLogo.jpeg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="{{ asset('js/toast.js') }}"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="{{ route('nail-artist.dashboard') }}" class="text-2xl font-bold text-pink-600">Cizy Nails - Artist Panel</a>
                </div>
                <div class="flex gap-4 items-center">
                    <span class="text-gray-600">{{ auth()->user()->name }}</span>
                    <a href="{{ route('profile.edit') }}" class="text-gray-600 hover:text-pink-600 transition">Edit Profile</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-600 hover:text-gray-900">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar Navigation -->
    <div class="flex">
        <div class="w-64 bg-white shadow-sm min-h-screen">
            <nav class="p-6 space-y-2">
                <a href="{{ route('nail-artist.dashboard') }}" class="block px-4 py-2 rounded-lg bg-pink-100 text-pink-600">
                    Dashboard
                </a>
                <a href="{{ route('nail-artist.bookings') }}" class="block px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100">
                    All Bookings
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <h1 class="text-3xl font-bold mb-8">Dashboard</h1>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Today's Bookings</p>
                    <p class="text-3xl font-bold text-pink-600">{{ $todayBookings }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Completed Today</p>
                    <p class="text-3xl font-bold text-green-600">{{ $completedToday }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Pending Bookings</p>
                    <p class="text-3xl font-bold text-yellow-600">{{ $pendingBookings }}</p>
                </div>
            </div>

            <!-- Upcoming Bookings -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Upcoming Bookings</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Customer</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Phone</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Service</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Date & Time</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($upcomingBookings as $booking)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div class="font-semibold">{{ $booking->customer_name ?? $booking->user?->name ?? 'Unknown' }}</div>
                                        <div class="text-gray-500 text-xs">{{ $booking->user?->email ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{!! \App\Helpers\WhatsAppHelper::formatPhoneWithLink($booking->customer_phone ?? $booking->user?->phone) !!}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->service?->name ?? 'Unknown' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $booking->booking_date->format('M d, Y') }}<br>
                                        <span class="text-gray-600">{{ $booking->booking_time }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-3 py-1 rounded-full text-sm font-semibold 
                                            {{ $booking->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                               ($booking->status === 'confirmed' ? 'bg-blue-100 text-blue-800' :
                                               ($booking->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm space-x-2">
                                        @if($booking->status === 'pending' || $booking->status === 'confirmed')
                                            <button onclick="updateStatus({{ $booking->id }}, 'completed')" class="text-green-600 hover:text-green-800 font-semibold">
                                                ✓ Complete
                                            </button>
                                        @endif
                                        @if($booking->status !== 'cancelled')
                                            <button onclick="updateStatus({{ $booking->id }}, 'cancelled')" class="text-red-600 hover:text-red-800">
                                                Cancel
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No upcoming bookings</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $upcomingBookings->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateStatus(bookingId, status) {
            const statusText = status === 'completed' ? 'completed' : status === 'confirmed' ? 'confirmed' : status;
            showConfirm(`Are you sure you want to mark this booking as ${statusText}?`, () => {
                fetch(`/nail-artist/bookings/${bookingId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Error updating status', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error updating status', 'error');
            });
            }, null, { title: 'Update Booking Status', confirmText: 'Yes, Update', type: 'info' });
        }
    </script>
</body>
</html>
