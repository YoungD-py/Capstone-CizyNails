<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Cizy Nails Admin</title>
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
                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100">
                    Dashboard
                </a>
                <a href="{{ route('admin.bookings') }}" class="block px-4 py-2 rounded-lg bg-pink-100 text-pink-600">
                    Bookings
                </a>
                <a href="{{ route('admin.services') }}" class="block px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100">
                    Services
                </a>
                <a href="{{ route('admin.schedules') }}" class="block px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100">
                    Schedules
                </a>
                <a href="{{ route('admin.customers') }}" class="block px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100">
                    Customers
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold">Manage Bookings</h1>
                <button onclick="openAddBookingModal()" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 font-semibold">
                    + Add Appointment
                </button>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <form method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal</label>
                            <input type="date" name="date" value="{{ request('date') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Payment Status</label>
                            <select name="payment_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                                <option value="">Semua Payment</option>
                                <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Service</label>
                            <select name="service_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                                <option value="">Semua Service</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Urutan</label>
                            <select name="sort" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                                <option value="latest" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>Terbaru</option>
                                <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Terlama</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="w-full px-4 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 font-semibold transition">Filter</button>
                            <a href="{{ route('admin.bookings') }}" class="w-full px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 font-semibold transition text-center">Reset</a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-gray-800">Bookings List</h2>
                <button onclick="bulkDelete()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-semibold">Delete Selected</button>
            </div>

            <!-- Bookings Table -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900 w-10">
                                <input type="checkbox" id="selectAll" class="w-4 h-4">
                            </th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Customer</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Phone</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Service</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Date & Time</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Payment</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Price</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($bookings as $booking)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 text-sm">
                                    <input type="checkbox" class="row-checkbox w-4 h-4" value="{{ $booking->id }}">
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    @if($booking->customer_name)
                                        <div class="font-semibold">{{ $booking->customer_name }}</div>
                                        <div class="text-gray-500 text-xs">{{ $booking->user?->email ?? 'N/A' }}</div>
                                    @else
                                        <div class="font-semibold">{{ $booking->user?->name ?? 'Unknown' }}</div>
                                        <div class="text-gray-500 text-xs">{{ $booking->user?->email ?? 'N/A' }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->customer_phone ?? $booking->user?->phone ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->service?->name ?? 'Unknown' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->booking_date->format('M d, Y') }} at {{ $booking->booking_time }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $booking->status === 'confirmed' ? 'bg-green-100 text-green-800' : ($booking->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-3 py-1 rounded-full text-sm font-semibold {{ in_array($booking->payment_status, ['paid', 'verified']) ? 'bg-blue-100 text-blue-800' : ($booking->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($booking->payment_status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-pink-600">Rp. {{ number_format($booking->price, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm space-y-1">
                                    @if($booking->payment_proof_path)
                                        <button onclick="viewPaymentProof('{{ asset('storage/' . $booking->payment_proof_path) }}')" class="block text-blue-600 hover:text-blue-800">View Proof</button>
                                    @endif
                                    @if($booking->payment_status === 'pending' && $booking->payment_proof_path)
                                        <button onclick="verifyPayment({{ $booking->id }})" class="block text-green-600 hover:text-green-800">Verify</button>
                                        <button onclick="rejectPayment({{ $booking->id }})" class="block text-red-600 hover:text-red-800">Reject</button>
                                    @endif
                                    <button onclick="deleteBooking({{ $booking->id }})" class="block text-red-600 hover:text-red-800">Delete</button>
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

    <!-- Payment Proof Modal -->
    <div id="paymentProofModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-8 max-w-2xl w-full mx-4">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">Payment Proof</h2>
                <button onclick="closePaymentProof()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            </div>
            <img id="paymentProofImage" src="/placeholder.svg" alt="Payment Proof" class="w-full max-h-96 object-contain">
        </div>
    </div>

    <script>
        function viewPaymentProof(imageUrl) {
            document.getElementById('paymentProofImage').src = imageUrl;
            document.getElementById('paymentProofModal').classList.remove('hidden');
        }

        function closePaymentProof() {
            document.getElementById('paymentProofModal').classList.add('hidden');
        }

        function verifyPayment(bookingId) {
            showConfirm('Are you sure you want to verify this payment?', () => {
                fetch(`/admin/bookings/${bookingId}/verify-payment`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Payment verified successfully!', 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast('Error: ' + (data.message || 'Failed to verify payment'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error verifying payment', 'error');
                });
            }, null, { title: 'Verify Payment', confirmText: 'Verify', type: 'info' });
        }

        function rejectPayment(bookingId) {
            showConfirm('Are you sure you want to reject this payment?', () => {
                fetch(`/admin/bookings/${bookingId}/reject-payment`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Payment rejected!', 'warning');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast('Error: ' + (data.message || 'Failed to reject payment'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error rejecting payment', 'error');
                });
            }, null, { title: 'Reject Payment', confirmText: 'Reject', type: 'danger' });
        }

        function deleteBooking(bookingId) {
            showConfirm('Are you sure you want to delete this booking? This action cannot be undone.', () => {
                fetch(`/admin/bookings/${bookingId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Booking deleted successfully', 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast('Error: ' + (data.message || 'Failed to delete booking'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error deleting booking', 'error');
                });
            }, null, { title: 'Delete Booking', confirmText: 'Delete', type: 'danger' });
        }

        // Bulk delete handling
        const selectAll = document.getElementById('selectAll');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');

        if (selectAll) {
            selectAll.addEventListener('change', (e) => {
                rowCheckboxes.forEach(cb => cb.checked = e.target.checked);
            });
        }

        function bulkDelete() {
            const ids = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
            if (ids.length === 0) {
                showToast('Please select at least one booking to delete', 'warning');
                return;
            }

            showConfirm(`Delete ${ids.length} selected booking(s)? This action cannot be undone.`, () => {
                fetch('/admin/bookings/bulk-delete', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ ids })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Selected bookings deleted successfully', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Error: ' + (data.message || 'Failed to delete bookings'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error deleting bookings', 'error');
            });
            }, null, { title: 'Delete Multiple Bookings', confirmText: 'Delete All', type: 'danger' });
        }

        // Add Booking Modal Functions
        function openAddBookingModal() {
            document.getElementById('addBookingModal').classList.remove('hidden');
            loadCustomers();
            loadServices();
        }

        function closeAddBookingModal() {
            document.getElementById('addBookingModal').classList.add('hidden');
            document.getElementById('addBookingForm').reset();
        }

        async function loadCustomers() {
            try {
                const response = await fetch('/admin/api/customers-list', {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });
                const customers = await response.json();
                const hiddenInput = document.getElementById('bookingCustomer');
                
                // Find user@cizy.com and set as default
                const defaultUser = customers.find(c => c.email === 'user@cizy.com');
                
                if (defaultUser) {
                    hiddenInput.value = defaultUser.id;
                } else {
                    console.error('Default user not found');
                    showToast('Default user (user@cizy.com) not found. Please contact administrator.', 'error');
                }
            } catch (error) {
                console.error('Error loading customers:', error);
                showToast('Error loading customer data', 'error');
            }
        }

        async function loadServices() {
            try {
                const response = await fetch('/admin/api/services-list', {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });
                const services = await response.json();
                const select = document.getElementById('bookingService');
                select.innerHTML = '<option value="">Select Service</option>' + 
                    services.map(s => `<option value="${s.id}" data-type="${s.type}" data-duration="${s.duration_minutes}">${s.name} - Rp ${s.price.toLocaleString()} (${s.duration_minutes} min)</option>`).join('');
            } catch (error) {
                console.error('Error loading services:', error);
            }
        }

        async function loadAvailableTimesAdmin() {
            const serviceId = document.getElementById('bookingService').value;
            const date = document.getElementById('bookingDate').value;
            const timeSlotsDiv = document.getElementById('adminTimeSlots');
            const bookingTimeInput = document.getElementById('bookingTime');

            if (!serviceId || !date) {
                timeSlotsDiv.innerHTML = '<p class="text-gray-500 text-sm col-span-4">Select service and date first</p>';
                return;
            }

            try {
                const response = await fetch(`/api/bookings/available-times?service_id=${serviceId}&date=${date}`);
                const data = await response.json();

                if (!data.times || data.times.length === 0) {
                    timeSlotsDiv.innerHTML = '<p class="text-gray-500 text-sm col-span-4">No available times</p>';
                    return;
                }

                timeSlotsDiv.innerHTML = data.times.map(slot => `
                    <button type="button" 
                            class="admin-time-slot px-3 py-2 rounded-lg border-2 transition ${slot.available ? 'border-gray-300 hover:border-green-600 cursor-pointer' : 'border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed'}"
                            data-time="${slot.time}"
                            ${!slot.available ? 'disabled' : ''}>
                        ${slot.time}
                    </button>
                `).join('');

                document.querySelectorAll('.admin-time-slot:not(:disabled)').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        document.querySelectorAll('.admin-time-slot').forEach(b => b.classList.remove('border-green-600', 'bg-green-50'));
                        btn.classList.add('border-green-600', 'bg-green-50');
                        bookingTimeInput.value = btn.dataset.time;
                    });
                });
            } catch (error) {
                console.error('Error loading times:', error);
                timeSlotsDiv.innerHTML = '<p class="text-red-500 text-sm col-span-4">Error loading times</p>';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const addBookingForm = document.getElementById('addBookingForm');
            if (!addBookingForm) return;

            addBookingForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const formData = new FormData(e.target);
                const data = Object.fromEntries(formData);

                // Validation
                if (!data.user_id) {
                    showToast('Error: Customer data tidak ditemukan. Silakan refresh halaman.', 'error');
                    return;
                }

                if (!data.customer_name || !data.customer_phone) {
                    showToast('Nama customer dan nomor telepon wajib diisi', 'warning');
                    return;
                }

                if (!data.booking_time) {
                    showToast('Silakan pilih waktu booking', 'warning');
                    return;
                }

                console.log('Sending booking data:', data);

                try {
                    const response = await fetch('/admin/bookings/create', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();
                    console.log('Response:', result);

                    if (response.ok) {
                        showToast('Booking berhasil dibuat!', 'success');
                        closeAddBookingModal();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        const errorMessage = result.message || 'Gagal membuat booking';
                        const errorDetails = result.errors ? ': ' + Object.values(result.errors).flat().join(', ') : '';
                        const debugInfo = result.error ? ' [Debug: ' + result.error + ']' : '';
                        showToast('Error: ' + errorMessage + errorDetails + debugInfo, 'error', 8000);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Error creating booking: ' + error.message, 'error');
                }
            });
        });
    </script>

    <!-- Add Booking Modal -->
    <div id="addBookingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <h2 class="text-2xl font-bold mb-6">Add Manual Booking</h2>
            <p class="text-sm text-gray-600 mb-4">For customers who booked via WhatsApp and paid directly</p>
            
            <form id="addBookingForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Customer *</label>
                    <input type="text" value="Walk-in Customer (user@cizy.com)" readonly class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed">
                    <input type="hidden" id="bookingCustomer" name="user_id" required>
                    <p class="text-xs text-gray-500 mt-1">Email akan tercatat sebagai user@cizy.com</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Nama Customer *</label>
                    <input type="text" name="customer_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Masukkan nama customer">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Nomor Telepon *</label>
                    <input type="text" name="customer_phone" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Contoh: 081234567890">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Service *</label>
                    <select id="bookingService" name="service_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" onchange="loadAvailableTimesAdmin()">
                        <option value="">Loading...</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Booking Date *</label>
                    <input type="date" id="bookingDate" name="booking_date" min="{{ date('Y-m-d') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" onchange="loadAvailableTimesAdmin()">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Select Time *</label>
                    <div id="adminTimeSlots" class="grid grid-cols-4 gap-2 mb-2">
                        <p class="text-gray-500 text-sm col-span-4">Select service and date first</p>
                    </div>
                    <input type="hidden" id="bookingTime" name="booking_time" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Notes</label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="Additional notes..."></textarea>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800">
                        <strong>Note:</strong> Booking akan dibuat dengan status <strong>"Confirmed"</strong> dan payment status <strong>"Paid"</strong> (pembayaran manual diterima). Email tercatat sebagai <strong>user@cizy.com</strong>, nama dan nomor telepon sesuai dengan yang Anda masukkan.
                    </p>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 font-semibold">
                        Create Booking
                    </button>
                    <button type="button" onclick="closeAddBookingModal()" class="flex-1 bg-gray-300 text-gray-900 py-3 rounded-lg hover:bg-gray-400 font-semibold">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
