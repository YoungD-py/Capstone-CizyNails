<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Cizy Nails</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('img/cizyLogo.jpeg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="{{ route('admin.dashboard') }}" class="text-2xl font-bold text-pink-600">Cizy Nails Admin</a>
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
                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-pink-100 text-pink-600' : 'text-gray-600 hover:bg-gray-100' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.bookings') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('admin.bookings') ? 'bg-pink-100 text-pink-600' : 'text-gray-600 hover:bg-gray-100' }}">
                    Bookings
                </a>
                <a href="{{ route('admin.services') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('admin.services') ? 'bg-pink-100 text-pink-600' : 'text-gray-600 hover:bg-gray-100' }}">
                    Services
                </a>
                <a href="{{ route('admin.schedules') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('admin.schedules') ? 'bg-pink-100 text-pink-600' : 'text-gray-600 hover:bg-gray-100' }}">
                    Schedules
                </a>
                <a href="{{ route('admin.customers') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('admin.customers') ? 'bg-pink-100 text-pink-600' : 'text-gray-600 hover:bg-gray-100' }}">
                    Customers
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <h1 class="text-3xl font-bold mb-8">Dashboard</h1>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Total Bookings</p>
                    <p class="text-3xl font-bold text-pink-600">{{ $totalBookings }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Total Customers</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $totalCustomers }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Total Services</p>
                    <p class="text-3xl font-bold text-green-600">{{ $totalServices }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <p class="text-gray-600 text-sm">Today's Bookings</p>
                    <p class="text-3xl font-bold text-purple-600">{{ $todayBookings }}</p>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Recent Bookings</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Customer</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Service</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Date & Time</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Payment</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($recentBookings as $booking)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div class="font-semibold">{{ $booking->user?->name ?? 'Unknown' }}</div>
                                        <div class="text-gray-500 text-xs">{{ $booking->user?->email ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->service?->name ?? 'Unknown' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->booking_date->format('M d, Y') }} <br><span class="text-gray-600">{{ $booking->booking_time }}</span></td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $booking->status === 'confirmed' ? 'bg-green-100 text-green-800' : ($booking->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $booking->payment_status === 'paid' ? 'bg-blue-100 text-blue-800' : ($booking->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ ucfirst($booking->payment_status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-semibold text-pink-600">Rp.{{ number_format($booking->price, 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No bookings yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
