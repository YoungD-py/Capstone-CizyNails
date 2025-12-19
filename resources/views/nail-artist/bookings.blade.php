<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Bookings - Cizy Nails Artist</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="{{ route('nail-artist.dashboard') }}" class="text-2xl font-bold text-pink-600">Cizy Nails - Artist Panel</a>
                </div>
                <div class="flex gap-4">
                    <span class="text-gray-600">{{ auth()->user()->name }}</span>
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
                <a href="{{ route('nail-artist.dashboard') }}" class="block px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100">
                    Dashboard
                </a>
                <a href="{{ route('nail-artist.bookings') }}" class="block px-4 py-2 rounded-lg bg-pink-100 text-pink-600">
                    All Bookings
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <h1 class="text-3xl font-bold mb-8">All Bookings</h1>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <form method="GET" class="flex gap-4">
                    <input type="date" name="date" value="{{ request('date') }}" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    <button type="submit" class="px-6 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700">Filter</button>
                    <a href="{{ route('nail-artist.bookings') }}" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">Reset</a>
                </form>
            </div>

            <!-- Bookings Table -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Customer</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Phone</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Service</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Date & Time</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Duration</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Payment</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($bookings as $booking)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="font-semibold">{{ $booking->user->name }}</div>
                                    <div class="text-gray-500 text-xs">{{ $booking->user->email }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->user->phone ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->service->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $booking->booking_date->format('M d, Y') }}<br>
                                    <span class="text-gray-600">{{ $booking->booking_time }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->total_duration_minutes }} min</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-3 py-1 rounded-full text-sm font-semibold 
                                        {{ $booking->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                           ($booking->status === 'confirmed' ? 'bg-blue-100 text-blue-800' :
                                           ($booking->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-3 py-1 rounded-full text-sm font-semibold 
                                        {{ $booking->payment_status === 'paid' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ ucfirst($booking->payment_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex flex-col gap-2">
                                        @if($booking->status === 'pending' || $booking->status === 'confirmed')
                                            <button onclick="updateStatus({{ $booking->id }}, 'completed')" class="text-green-600 hover:text-green-800 font-semibold text-left">
                                                ✓ Complete
                                            </button>
                                        @endif
                                        @if($booking->status === 'completed')
                                            <button onclick="updateStatus({{ $booking->id }}, 'confirmed')" class="text-blue-600 hover:text-blue-800 text-left">
                                                Undo Complete
                                            </button>
                                        @endif
                                        @if($booking->status !== 'cancelled')
                                            <button onclick="updateStatus({{ $booking->id }}, 'cancelled')" class="text-red-600 hover:text-red-800 text-left">
                                                Cancel
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500">No bookings found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $bookings->links() }}
            </div>
        </div>
    </div>

    <script>
        function updateStatus(bookingId, status) {
            const statusText = status === 'completed' ? 'completed' : (status === 'cancelled' ? 'cancelled' : 'confirmed');
            if (!confirm(`Are you sure you want to mark this booking as ${statusText}?`)) {
                return;
            }

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
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert('Error updating status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating status');
            });
        }
    </script>
</body>
</html>
