<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Cizy Nails</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    <script src="{{ asset('js/toast.js') }}"></script>
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
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition {{ $booking->payment_status === 'pending' ? 'border-yellow-300 bg-yellow-50' : '' }}">
                            <div class="flex justify-between items-start">
                                <div class="flex-1 cursor-pointer" onclick="openBookingDetail({{ $booking->id }})">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $booking->service->name }}</h3>
                                    <p class="text-gray-600 text-sm mt-1">
                                        📅 {{ $booking->booking_date->format('M d, Y') }} at {{ \Carbon\Carbon::parse($booking->booking_time)->format('H:i') }}
                                    </p>
                                    <p class="text-gray-600 text-sm">
                                        ⏱️ {{ $booking->total_duration_minutes }} minutes
                                    </p>
                                    @if($booking->notes)
                                        <p class="text-gray-600 text-sm mt-2">
                                            📝 {{ $booking->notes }}
                                        </p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <p class="text-pink-600 font-bold text-lg">Rp.{{ number_format($booking->price, 0) }}</p>
                                    <span class="inline-block mt-2 px-3 py-1 {{ $booking->status === 'confirmed' ? 'bg-green-100 text-green-800' : ($booking->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }} text-sm rounded-full">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                    <span class="inline-block mt-1 px-3 py-1 {{ $booking->payment_status === 'paid' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800' }} text-sm rounded-full">
                                        {{ $booking->payment_status === 'paid' ? 'Paid' : 'Unpaid' }}
                                    </span>
                                    
                                    @if($booking->payment_status === 'pending')
                                        <button onclick="retryPayment({{ $booking->id }})" class="block mt-2 w-full bg-green-600 text-white px-3 py-2 rounded-lg hover:bg-green-700 text-sm font-semibold">
                                            💳 Pay Now
                                        </button>
                                    @endif
                                    
                                    @if($booking->reschedule_count < 1 && $booking->status !== 'cancelled' && $booking->status !== 'completed')
                                        <button onclick="openRescheduleModal({{ $booking->id }}, '{{ $booking->service->id }}', '{{ $booking->booking_date->format('Y-m-d') }}', '{{ $booking->booking_time }}')" class="block mt-2 w-full bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 text-sm font-semibold">
                                            📅 Reschedule
                                        </button>
                                    @elseif($booking->reschedule_count >= 1)
                                        <span class="block mt-2 text-xs text-gray-500 italic">Reschedule sudah digunakan</span>
                                    @endif
                                    
                                    <button onclick="cancelBooking({{ $booking->id }})" class="block mt-2 w-full text-red-600 hover:text-red-800 text-sm">
                                        Cancel Booking
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
                                    <p class="text-pink-600 font-bold">Rp.{{ number_format($booking->price, 0) }}</p>
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

    <!-- Reschedule Modal -->
    <div id="rescheduleModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full mx-4">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold">Reschedule Appointment</h2>
                    <button onclick="closeRescheduleModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                </div>
                
                <div class="mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-sm text-yellow-800">⚠️ <strong>Perhatian:</strong> Anda hanya dapat melakukan reschedule <strong>sekali</strong> untuk setiap booking.</p>
                </div>

                <form id="rescheduleForm" class="space-y-4">
                    <input type="hidden" id="rescheduleBookingId">
                    <input type="hidden" id="rescheduleServiceId">
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">Tanggal Baru</label>
                        <input type="date" id="rescheduleDate" min="{{ date('Y-m-d') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">Waktu Baru</label>
                        <div id="rescheduleTimeSlots" class="grid grid-cols-4 gap-2">
                            <p class="text-gray-500 text-sm col-span-4">Pilih tanggal terlebih dahulu</p>
                        </div>
                        <input type="hidden" id="rescheduleTime">
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 font-semibold">
                            Konfirmasi Reschedule
                        </button>
                        <button type="button" onclick="closeRescheduleModal()" class="flex-1 bg-gray-300 text-gray-800 px-4 py-3 rounded-lg hover:bg-gray-400 font-semibold">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openBookingDetail(bookingId) {
            fetch(`/api/bookings/${bookingId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'include'
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
                                <p class="text-lg font-semibold text-pink-600">Rp.${Math.round(parseFloat(booking.price)).toLocaleString('id-ID')}</p>
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
                showToast('Error loading booking details', 'error');
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
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'include'
                })
                .then(response => response.json())
                .then(data => {
                    showToast('Appointment cancelled successfully', 'success');
                    setTimeout(() => location.reload(), 1000);
                })
                .catch(error => {
                    showToast('Error cancelling appointment', 'error');
                    console.error(error);
                });
            }, null, { title: 'Cancel Appointment', confirmText: 'Yes, Cancel', type: 'danger' });
        }

        function retryPayment(bookingId) {
            fetch(`/api/bookings/${bookingId}/retry-payment`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
                if (data.snap_token) {
                    if (!window.snap || typeof window.snap.pay !== 'function') {
                        showToast('Payment UI failed to load. Please refresh and try again.', 'error');
                        return;
                    }
                    snap.pay(data.snap_token, {
                        onSuccess: function(result) {
                            console.log('Payment success:', result);
                            showToast('Payment successful! Your booking is confirmed.', 'success');
                            setTimeout(() => location.reload(), 2000);
                        },
                        onPending: function(result) {
                            console.log('Payment pending:', result);
                            showToast('Payment is being processed. Please wait for confirmation.', 'info');
                            setTimeout(() => location.reload(), 2000);
                        },
                        onError: function(result) {
                            console.log('Payment error:', result);
                            showToast('Payment failed. Please try again.', 'error');
                        },
                        onClose: function() {
                            console.log('Payment dialog closed');
                        }
                    });
                } else {
                    showToast('Error: ' + (data.message || 'Failed to initiate payment'), 'error');
                }
            })
            .catch(error => {
                showToast('Error initiating payment', 'error');
                console.error(error);
            });
        }

        // Reschedule functions
        function openRescheduleModal(bookingId, serviceId, currentDate, currentTime) {
            document.getElementById('rescheduleBookingId').value = bookingId;
            document.getElementById('rescheduleServiceId').value = serviceId;
            document.getElementById('rescheduleDate').value = currentDate;
            document.getElementById('rescheduleModal').classList.remove('hidden');
            
            // Load available times for current date
            loadRescheduleAvailableTimes();
        }

        function closeRescheduleModal() {
            document.getElementById('rescheduleModal').classList.add('hidden');
            document.getElementById('rescheduleForm').reset();
        }

        async function loadRescheduleAvailableTimes() {
            const serviceId = document.getElementById('rescheduleServiceId').value;
            const date = document.getElementById('rescheduleDate').value;
            const timeSlotsDiv = document.getElementById('rescheduleTimeSlots');
            const rescheduleTimeInput = document.getElementById('rescheduleTime');

            if (!serviceId || !date) {
                timeSlotsDiv.innerHTML = '<p class="text-gray-500 text-sm col-span-4">Pilih tanggal terlebih dahulu</p>';
                return;
            }

            try {
                const response = await fetch(`/api/bookings/available-times?service_id=${serviceId}&date=${date}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (!data.times || data.times.length === 0) {
                    timeSlotsDiv.innerHTML = '<p class="text-gray-500 text-sm col-span-4">Tidak ada waktu tersedia untuk tanggal ini</p>';
                    return;
                }

                timeSlotsDiv.innerHTML = data.times.map(slot => `
                    <button type="button" 
                            class="reschedule-time-slot px-3 py-2 rounded-lg border-2 transition ${slot.available ? 'border-gray-300 hover:border-blue-600 cursor-pointer' : 'border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed'}"
                            data-time="${slot.time}"
                            ${!slot.available ? 'disabled' : ''}>
                        ${slot.time}
                    </button>
                `).join('');

                // Add click handlers to time slots
                document.querySelectorAll('.reschedule-time-slot:not(:disabled)').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        document.querySelectorAll('.reschedule-time-slot').forEach(b => b.classList.remove('border-blue-600', 'bg-blue-50'));
                        btn.classList.add('border-blue-600', 'bg-blue-50');
                        rescheduleTimeInput.value = btn.dataset.time;
                    });
                });
            } catch (error) {
                console.error('Error loading times:', error);
                timeSlotsDiv.innerHTML = '<p class="text-red-500 text-sm col-span-4">Error loading available times. Please try again.</p>';
            }
        }

        // Event listener for reschedule date change
        document.addEventListener('DOMContentLoaded', function() {
            const rescheduleDate = document.getElementById('rescheduleDate');
            if (rescheduleDate) {
                rescheduleDate.addEventListener('change', loadRescheduleAvailableTimes);
            }
        });

        // Handle reschedule form submission
        document.getElementById('rescheduleForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const bookingId = document.getElementById('rescheduleBookingId').value;
            const newDate = document.getElementById('rescheduleDate').value;
            const newTime = document.getElementById('rescheduleTime').value;

            if (!newTime) {
                showToast('Silakan pilih waktu baru untuk reschedule', 'warning');
                return;
            }

            // Validate that the selected date and time is not in the past
            const selectedDateTime = new Date(`${newDate}T${newTime}:00`);
            const now = new Date();

            if (selectedDateTime < now) {
                showToast('Tidak dapat reschedule ke waktu yang sudah berlalu. Silakan pilih waktu yang lain.', 'warning');
                return;
            }

            showConfirm('Apakah Anda yakin ingin reschedule appointment ini? Anda hanya dapat reschedule sekali.', async () => {
                try {
                const response = await fetch(`/api/bookings/${bookingId}/reschedule`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        booking_date: newDate,
                        booking_time: newTime
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    showToast('✅ ' + result.message, 'success');
                    closeRescheduleModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    const errorMessage = result.message || 'Gagal melakukan reschedule';
                    const errorDetails = result.errors ? ': ' + Object.values(result.errors).flat().join(', ') : '';
                    showToast('Error: ' + errorMessage + errorDetails, 'error', 5000);
                }
            } catch (error) {
                console.error('Reschedule error:', error);
                showToast('Terjadi kesalahan saat reschedule. Silakan coba lagi.', 'error');
            }
            }, null, { title: 'Reschedule Appointment', confirmText: 'Yes, Reschedule', type: 'warning' });
        });
    </script>
</body>
</html>
