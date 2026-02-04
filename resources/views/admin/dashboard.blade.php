<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Cizy Nails</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('img/cizyLogo.jpeg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="{{ route('admin.dashboard') }}" class="text-2xl font-bold text-pink-600">Cizy Nails Admin</a>
                </div>
                <div class="flex gap-4 items-center">
                    <!-- Notification Bell -->
                    <div class="relative">
                        <button id="notificationBtn" class="relative text-gray-600 hover:text-pink-600 transition text-xl">
                            <i class="fas fa-bell"></i>
                            <span id="unreadBadge" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center {{ $unreadNotifications > 0 ? '' : 'hidden' }}">
                                {{ $unreadNotifications }}
                            </span>
                        </button>
                        
                        <!-- Notification Dropdown -->
                        <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl z-50 max-h-96 overflow-y-auto">
                            <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                                <h3 class="font-semibold text-gray-800">Notifikasi</h3>
                                @if($unreadNotifications > 0)
                                    <button id="markAllRead" class="text-xs text-pink-600 hover:text-pink-700">Tandai semua dibaca</button>
                                @endif
                            </div>
                            <div id="notificationList"></div>
                        </div>
                    </div>

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
    <div class="flex flex-col md:flex-row">
        <div class="w-full md:w-64 bg-white shadow-sm md:min-h-screen">
            <nav class="p-6 space-y-2">
                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-pink-100 text-pink-600' : 'text-gray-600 hover:bg-gray-100' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.bookings') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('admin.bookings') ? 'bg-pink-100 text-pink-600' : 'text-gray-600 hover:bg-gray-100' }}">
                    Bookings
                </a>
                   <a href="{{ route('admin.types.index') }}" class="block px-4 py-2 rounded-lg {{ request()->routeIs('admin.types.*') ? 'bg-pink-100 text-pink-600' : 'text-gray-600 hover:bg-gray-100' }}">
                       Types
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
        <div class="flex-1 p-4 md:p-8">
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
                    <p class="text-gray-600 text-sm">Total Revenue</p>
                    <p class="text-3xl font-bold text-emerald-600">Rp. {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- Quick Stats Row -->
            <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg shadow-md p-6 mb-8 border border-yellow-200">
                <p class="text-gray-700 text-sm font-semibold">📊 Today's Bookings: <span class="text-xl font-bold text-orange-600">{{ $todayBookings }}</span></p>
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

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $recentBookings->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        const notificationBtn = document.getElementById('notificationBtn');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const notificationList = document.getElementById('notificationList');
        const markAllRead = document.getElementById('markAllRead');
        const unreadBadge = document.getElementById('unreadBadge');
        const csrfToken = '{{ csrf_token() }}';

        // Toggle notification dropdown
        notificationBtn.addEventListener('click', () => {
            notificationDropdown.classList.toggle('hidden');
            if (!notificationDropdown.classList.contains('hidden')) {
                loadNotifications();
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.relative')) {
                notificationDropdown.classList.add('hidden');
            }
        });

        // Load notifications
        async function loadNotifications() {
            try {
                const response = await fetch('/api/admin/notifications', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'include'
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.error || 'Unknown error');
                }
                
                updateBadge(data.unread_count);
                renderNotifications(data.notifications);
            } catch (error) {
                console.error('Error loading notifications:', error);
                notificationList.innerHTML = '<p class="p-4 text-center text-gray-500">Error: ' + error.message + '</p>';
            }
        }

        // Render notifications
        function renderNotifications(notifications) {
            if (notifications.length === 0) {
                notificationList.innerHTML = '<p class="p-4 text-center text-gray-500">No notifications</p>';
                return;
            }

            notificationList.innerHTML = notifications.map(notif => `
                <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition ${notif.is_read ? '' : 'bg-blue-50'} cursor-pointer" onclick="markAsRead(${notif.id})">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-800">${notif.title}</h4>
                            <p class="text-sm text-gray-600 mt-1">${notif.customer_name}</p>
                            <p class="text-xs text-gray-400 mt-2">${notif.created_at}</p>
                        </div>
                        ${notif.is_read ? '' : '<span class="bg-red-500 h-2 w-2 rounded-full mt-1"></span>'}
                    </div>
                </div>
            `).join('');
        }

        // Mark notification as read
        async function markAsRead(id) {
            try {
                await fetch(`/api/admin/notifications/${id}/read`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'include'
                });
                loadNotifications();
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        }

        // Mark all as read
        markAllRead?.addEventListener('click', async () => {
            try {
                await fetch('/api/admin/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'include'
                });
                loadNotifications();
            } catch (error) {
                console.error('Error marking all as read:', error);
            }
        });

        // Update badge count
        function updateBadge(count) {
            if (count > 0) {
                unreadBadge.textContent = count;
                unreadBadge.classList.remove('hidden');
            } else {
                unreadBadge.classList.add('hidden');
            }
        }

        // Load notifications on page load
        loadNotifications();

        // Poll for new notifications every 30 seconds
        setInterval(loadNotifications, 30000);
    </script>
</body>
</html>
