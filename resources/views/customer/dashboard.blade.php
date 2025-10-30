<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Cizy Nails</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Added html2canvas for screenshot functionality -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
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
                        <!-- Added cursor-pointer and onclick to open detail modal -->
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition cursor-pointer" onclick="openBookingDetail({{ $booking->id }})">
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
                                    <!-- Added payment_status display -->
                                    <span class="inline-block mt-2 px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                    <span class="inline-block mt-1 px-3 py-1 {{ $booking->payment_status === 'paid' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800' }} text-sm rounded-full">
                                        Payment: {{ ucfirst($booking->payment_status) }}
                                    </span>
                                    <button onclick="event.stopPropagation(); cancelBooking({{ $booking->id }})" class="block mt-2 text-red-600 hover:text-red-800 text-sm">
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

    <!-- Added booking detail modal -->
    <div id="bookingDetailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full mx-4 max-h-96 overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold">Booking Details</h2>
                    <button onclick="closeBookingDetail()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                </div>
                
                <div id="bookingDetailContent" class="space-y-4">
                    <!-- Content will be loaded here -->
                </div>

                <div class="flex gap-4 mt-6">
                    <button onclick="downloadBookingDetail()" class="flex-1 bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700">
                        Download as Image
                    </button>
                    <button onclick="closeBookingDetail()" class="flex-1 bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openBookingDetail(bookingId) {
            fetch(`/api/bookings/${bookingId}`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                const booking = data.booking;
                const paymentStatusColor = booking.payment_status === 'paid' ? 'text-blue-600' : 'text-yellow-600';
                
                const html = `
                    <div id="detailContent" class="space-y-4">
                        <div class="border-b pb-4">
                            <h3 class="text-xl font-bold text-pink-600">${booking.service.name}</h3>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-600 text-sm">Booking Date</p>
                                <p class="text-lg font-semibold">${new Date(booking.booking_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Booking Time</p>
                                <p class="text-lg font-semibold">${booking.booking_time}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-600 text-sm">Duration</p>
                                <p class="text-lg font-semibold">${booking.total_duration_minutes} minutes</p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Price</p>
                                <p class="text-lg font-semibold text-pink-600">$${parseFloat(booking.price).toFixed(2)}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-600 text-sm">Customer Name</p>
                                <p class="text-lg font-semibold">${booking.user.name}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Phone</p>
                                <p class="text-lg font-semibold">${booking.user.phone || 'Not provided'}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-600 text-sm">Email</p>
                                <p class="text-lg font-semibold">${booking.user.email}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Booking Status</p>
                                <p class="text-lg font-semibold">${booking.status.charAt(0).toUpperCase() + booking.status.slice(1)}</p>
                            </div>
                        </div>

                        <div>
                            <p class="text-gray-600 text-sm">Payment Status</p>
                            <p class="text-lg font-semibold ${paymentStatusColor}">${booking.payment_status.charAt(0).toUpperCase() + booking.payment_status.slice(1)}</p>
                        </div>

                        ${booking.notes ? `
                        <div>
                            <p class="text-gray-600 text-sm">Notes</p>
                            <p class="text-lg font-semibold">${booking.notes}</p>
                        </div>
                        ` : ''}

                        ${booking.needs_removal ? `
                        <div>
                            <p class="text-gray-600 text-sm">Additional Service</p>
                            <p class="text-lg font-semibold">Removal included (+30 minutes)</p>
                        </div>
                        ` : ''}
                    </div>
                `;
                
                document.getElementById('bookingDetailContent').innerHTML = html;
                document.getElementById('bookingDetailModal').classList.remove('hidden');
            })
            .catch(error => {
                alert('Error loading booking details');
                console.error(error);
            });
        }

        function closeBookingDetail() {
            document.getElementById('bookingDetailModal').classList.add('hidden');
        }

        function downloadBookingDetail() {
            const element = document.getElementById('detailContent');
            const opt = {
                margin: 10,
                filename: 'booking-detail.png',
                image: { type: 'png' },
                html2canvas: { scale: 2 },
                jsPDF: { orientation: 'portrait', unit: 'mm', format: 'a4' }
            };

            html2canvas(element, { scale: 2, backgroundColor: '#ffffff' }).then(canvas => {
                const link = document.createElement('a');
                link.href = canvas.toDataURL('image/png');
                link.download = `booking-detail-${new Date().getTime()}.png`;
                link.click();
            });
        }

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
