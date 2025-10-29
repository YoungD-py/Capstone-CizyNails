<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Cizy Nails</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="{{ route('landing') }}" class="text-2xl font-bold text-pink-600">Cizy Nails</a>
                </div>
                <div class="flex gap-4">
                    <span class="text-gray-600">{{ $user->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-600 hover:text-gray-900">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Profile Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-2xl font-bold mb-4">My Profile</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-gray-600 text-sm">Name</p>
                    <p class="text-lg font-semibold">{{ $user->name }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Email</p>
                    <p class="text-lg font-semibold">{{ $user->email }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Phone</p>
                    <p class="text-lg font-semibold">{{ $user->phone ?? 'Not provided' }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Member Since</p>
                    <p class="text-lg font-semibold">{{ $user->created_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Upcoming Bookings -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Upcoming Appointments</h2>
                <a href="{{ route('booking.form') }}" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700">
                    Book New Appointment
                </a>
            </div>

            @if($upcomingBookings->count() > 0)
                <div class="space-y-4">
                    @foreach($upcomingBookings as $booking)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $booking->service->name }}</h3>
                                    <p class="text-gray-600 text-sm mt-1">
                                        📅 {{ $booking->booking_date->format('M d, Y') }} at {{ $booking->booking_time }}
                                    </p>
                                    <p class="text-gray-600 text-sm">
                                        ⏱️ {{ $booking->service->duration }} minutes
                                    </p>
                                    @if($booking->notes)
                                        <p class="text-gray-600 text-sm mt-2">
                                            📝 {{ $booking->notes }}
                                        </p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <p class="text-pink-600 font-bold text-lg">${{ number_format($booking->service->price, 2) }}</p>
                                    <span class="inline-block mt-2 px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                    <button onclick="cancelBooking({{ $booking->id }})" class="block mt-2 text-red-600 hover:text-red-800 text-sm">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                    <p class="text-blue-800 mb-4">You don't have any upcoming appointments</p>
                    <a href="{{ route('booking.form') }}" class="inline-block bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">
                        Book Your First Appointment
                    </a>
                </div>
            @endif
        </div>

        <!-- Past Bookings -->
        @if($pastBookings->count() > 0)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold mb-6">Past Appointments</h2>
                <div class="space-y-4">
                    @foreach($pastBookings as $booking)
                        <div class="border border-gray-200 rounded-lg p-4 opacity-75">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $booking->service->name }}</h3>
                                    <p class="text-gray-600 text-sm mt-1">
                                        📅 {{ $booking->booking_date->format('M d, Y') }} at {{ $booking->booking_time }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-pink-600 font-bold">${{ number_format($booking->service->price, 2) }}</p>
                                    <span class="inline-block mt-2 px-3 py-1 bg-gray-100 text-gray-800 text-sm rounded-full">
                                        Completed
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <script>
        function cancelBooking(bookingId) {
            if (confirm('Are you sure you want to cancel this appointment?')) {
                fetch(`/api/bookings/${bookingId}/cancel`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    alert('Appointment cancelled successfully');
                    location.reload();
                })
                .catch(error => {
                    alert('Error cancelling appointment');
                    console.error(error);
                });
            }
        }
    </script>
</body>
</html>
