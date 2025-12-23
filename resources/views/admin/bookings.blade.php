<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Cizy Nails Admin</title>
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
                <form method="GET" class="flex gap-4">
                    <input type="date" name="date" value="{{ request('date') }}" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    <select name="payment_status" class="px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">All Payment Status</option>
                        <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="verified" {{ request('payment_status') === 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="rejected" {{ request('payment_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    <button type="submit" class="px-6 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700">Filter</button>
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
                                    <div class="font-semibold">{{ $booking->user->name }}</div>
                                    <div class="text-gray-500 text-xs">{{ $booking->user->email }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->user->phone ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->service->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $booking->booking_date->format('M d, Y') }} at {{ $booking->booking_time }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $booking->status === 'confirmed' ? 'bg-green-100 text-green-800' : ($booking->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $booking->payment_status === 'paid' ? 'bg-blue-100 text-blue-800' : ($booking->payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
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
            if (confirm('Are you sure you want to verify this payment?')) {
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
                        alert('Payment verified successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to verify payment'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error verifying payment');
                });
            }
        }

        function rejectPayment(bookingId) {
            if (confirm('Are you sure you want to reject this payment?')) {
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
                        alert('Payment rejected!');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to reject payment'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error rejecting payment');
                });
            }
        }

        function deleteBooking(bookingId) {
            if (confirm('Are you sure you want to delete this booking?')) {
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
                        alert('Booking deleted');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to delete booking'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting booking');
                });
            }
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
                alert('Please select at least one booking to delete');
                return;
            }

            if (!confirm(`Delete ${ids.length} selected booking(s)?`)) {
                return;
            }

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
                    alert('Selected bookings deleted');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to delete bookings'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting bookings');
            });
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
                const select = document.getElementById('bookingCustomer');
                select.innerHTML = '<option value="">Select Customer</option>' + 
                    customers.map(c => `<option value="${c.id}">${c.name} - ${c.email}</option>`).join('');
            } catch (error) {
                console.error('Error loading customers:', error);
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

        document.getElementById('addBookingForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);

            if (!data.booking_time) {
                alert('Please select a time slot');
                return;
            }

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

                if (response.ok) {
                    alert('Booking created successfully!');
                    closeAddBookingModal();
                    location.reload();
                } else {
                    const errorMessage = result.message || 'Failed to create booking';
                    const errorDetails = result.errors ? '\n\n' + Object.values(result.errors).flat().join('\n') : '';
                    alert('Error: ' + errorMessage + errorDetails);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error creating booking');
            }
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
                    <select id="bookingCustomer" name="user_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                        <option value="">Loading...</option>
                    </select>
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
                        <strong>Note:</strong> Booking will be created with status <strong>"Confirmed"</strong> and payment status <strong>"Paid"</strong> (manual payment received).
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
