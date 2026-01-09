<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - Cizy Nails</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('img/cizyLogo.jpeg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Added Midtrans Snap script -->
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    <script src="{{ asset('js/toast.js') }}"></script>
    <style>
        .service-card {
            position: relative;
        }
        .service-card.selected {
            border-color: #ec4899;
            background-color: #fdf2f8;
            box-shadow: 0 4px 15px rgba(236, 72, 153, 0.2);
        }
        .service-card.selected .checkmark {
            display: block;
        }
    </style>
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
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-600 hover:text-gray-900">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white rounded-lg shadow-md p-8">
            <h1 class="text-3xl font-bold mb-8">Book Your Appointment</h1>

            <form id="bookingForm" class="space-y-6">
                @csrf

                <!-- Service Selection -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-4">Select Service</label>
                    <div id="serviceCards" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($services as $service)
                            <div class="service-card border-2 border-gray-200 rounded-xl p-4 cursor-pointer transition-all hover:border-pink-300 hover:shadow-md" 
                                 onclick="selectService({{ $service->id }}, '{{ $service->type }}', {{ $service->duration_minutes }}, {{ $service->enable_removal ? 1 : 0 }})"
                                 data-service-id="{{ $service->id }}"
                                 data-type="{{ $service->type }}"
                                 data-duration="{{ $service->duration_minutes }}"
                                 data-enable-removal="{{ $service->enable_removal ? 1 : 0 }}">
                                <div class="flex items-start gap-3">
                                    @if($service->image_path)
                                        <img src="{{ asset('storage/'.$service->image_path) }}" alt="{{ $service->name }}" class="w-16 h-16 object-cover rounded-lg">
                                    @else
                                        <div class="w-16 h-16 bg-gradient-to-br from-pink-400 to-rose-400 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <h3 class="font-bold text-gray-900 mb-1">{{ $service->name }}</h3>
                                        <p class="text-sm text-gray-600 mb-2">{{ Str::limit($service->description, 50) }}</p>
                                        <div class="flex items-center justify-between">
                                            <span class="text-pink-600 font-bold">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">{{ $service->duration_minutes }} min</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="checkmark hidden absolute top-2 right-2 bg-pink-600 text-white rounded-full p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <input type="hidden" id="serviceId" name="service_id" required>
                </div>

                <!-- Date Selection -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Select Date</label>
                    <input type="date" id="bookingDate" name="booking_date" value="{{ $date }}" min="{{ date('Y-m-d') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                </div>

                <!-- Time Selection -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Select Time</label>
                    <div id="timeSlots" class="grid grid-cols-4 gap-2">
                        <p class="text-gray-500 text-sm">Select a service and date first</p>
                    </div>
                    <input type="hidden" id="bookingTime" name="booking_time">
                </div>

                <!-- Add removal option field -->
                <div id="removalOptionContainer" class="hidden">
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Removal Option</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="needs_removal" value="0" checked class="mr-2">
                            <span class="text-gray-700">No removal needed</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="needs_removal" value="1" class="mr-2">
                            <span class="text-gray-700">There are nails/eyelash lama yang perlu di-remove <span class="text-pink-600 font-semibold">+30 min</span></span>
                        </label>
                    </div>
                    <p id="durationInfo" class="text-sm text-gray-600 mt-2"></p>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Additional Notes (Optional)</label>
                    <textarea name="notes" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent" placeholder="Any special requests or notes..."></textarea>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-pink-600 text-white py-3 rounded-lg hover:bg-pink-700 transition font-semibold">
                    Confirm Booking
                </button>
            </form>
        </div>
    </div>

    <!-- Replaced QRIS payment modal with Midtrans Snap integration -->
    <!-- Success Modal -->
    <div id="successModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full mx-4 text-center">
            <div class="mb-4">
                <svg class="w-16 h-16 mx-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold mb-2">Terimakasih Atas Booking Appointment Anda!</h2>
            <p class="text-gray-600 mb-6">Pembayaran Anda telah berhasil diproses. Silakan cek dashboard untuk detail booking Anda.</p>
            <button type="button" id="backToDashboard" class="w-full px-4 py-3 bg-pink-600 text-white rounded-lg hover:bg-pink-700 transition font-semibold">
                Kembali ke Dashboard
            </button>
        </div>
    </div>

    <script>
        // Check if there's booking intent from session
        const bookingIntent = @json(session('booking_intent'));
        
        const serviceIdInput = document.getElementById('serviceId');
        const dateInput = document.getElementById('bookingDate');
        const timeSlotsDiv = document.getElementById('timeSlots');
        const bookingTimeInput = document.getElementById('bookingTime');
        const bookingForm = document.getElementById('bookingForm');
        const removalOptionContainer = document.getElementById('removalOptionContainer');
        const durationInfo = document.getElementById('durationInfo');
        const successModal = document.getElementById('successModal');
        const backToDashboardBtn = document.getElementById('backToDashboard');

        let currentBookingId = null;
        let snapToken = null;
        let selectedServiceType = null;
        let selectedServiceDuration = null;
        let selectedServiceEnableRemoval = false;

        function selectService(serviceId, serviceType, duration, enableRemoval = 0) {
            // Remove selected class from all cards
            document.querySelectorAll('.service-card').forEach(card => {
                card.classList.remove('selected');
            });

            // Add selected class to clicked card
            const selectedCard = document.querySelector(`.service-card[data-service-id="${serviceId}"]`);
            if (selectedCard) {
                selectedCard.classList.add('selected');
            }

            // Set hidden input value
            serviceIdInput.value = serviceId;
            selectedServiceType = serviceType;
            selectedServiceDuration = duration;
            selectedServiceEnableRemoval = enableRemoval;

            // Show/hide removal option based on service settings
            if (enableRemoval && (serviceType === 'nails_art' || serviceType === 'eyelash')) {
                removalOptionContainer.classList.remove('hidden');
                updateDurationDisplay();
            } else {
                removalOptionContainer.classList.add('hidden');
                durationInfo.textContent = '';
            }

            // Reload available times if date is already selected
            if (dateInput.value) {
                loadAvailableTimes();
            }
        }

        function updateDurationDisplay() {
            if (!selectedServiceDuration) return;
            
            const needsRemoval = document.querySelector('input[name="needs_removal"]:checked').value === '1';
            const totalDuration = needsRemoval ? selectedServiceDuration + 30 : selectedServiceDuration;
            durationInfo.textContent = `Total duration: ${totalDuration} minutes`;
        }

        async function loadAvailableTimes() {
            const serviceId = serviceIdInput.value;
            const date = dateInput.value;
            const needsRemoval = document.querySelector('input[name="needs_removal"]:checked').value === '1' ? 1 : 0;

            if (!serviceId || !date) {
                timeSlotsDiv.innerHTML = '<p class="text-gray-500 text-sm">Select a service and date first</p>';
                return;
            }

            try {
                const response = await fetch(`/api/bookings/available-times?service_id=${serviceId}&date=${date}&needs_removal=${needsRemoval}`, {
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
                    timeSlotsDiv.innerHTML = '<p class="text-gray-500 text-sm col-span-4">No available times for this date</p>';
                    return;
                }

                timeSlotsDiv.innerHTML = data.times.map(slot => `
                    <button type="button" 
                            class="time-slot px-3 py-2 rounded-lg border-2 transition ${slot.available ? 'border-gray-300 hover:border-pink-600 cursor-pointer' : 'border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed'}"
                            data-time="${slot.time}"
                            ${!slot.available ? 'disabled' : ''}>
                        ${slot.time}
                    </button>
                `).join('');

                // Add click handlers to time slots
                document.querySelectorAll('.time-slot:not(:disabled)').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        document.querySelectorAll('.time-slot').forEach(b => b.classList.remove('border-pink-600', 'bg-pink-50'));
                        btn.classList.add('border-pink-600', 'bg-pink-50');
                        bookingTimeInput.value = btn.dataset.time;
                    });
                });
            } catch (error) {
                console.error('Error loading times:', error);
                timeSlotsDiv.innerHTML = '<p class="text-red-500 text-sm">Error loading available times. Please try again.</p>';
            }
        }

        document.querySelectorAll('input[name="needs_removal"]').forEach(radio => {
            radio.addEventListener('change', () => {
                updateDurationDisplay();
                // Reload available times when removal option changes
                if (dateInput.value) {
                    loadAvailableTimes();
                }
            });
        });

        dateInput.addEventListener('change', loadAvailableTimes);

        bookingForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!serviceIdInput.value) {
                showToast('Please select a service', 'warning');
                return;
            }

            if (!bookingTimeInput.value) {
                showToast('Please select a time slot', 'warning');
                return;
            }

            // Validate that the selected date and time is not in the past
            const selectedDate = dateInput.value;
            const selectedTime = bookingTimeInput.value;
            const selectedDateTime = new Date(`${selectedDate}T${selectedTime}:00`);
            const now = new Date();

            if (selectedDateTime < now) {
                showToast('Tidak dapat melakukan booking pada waktu yang sudah berlalu. Silakan pilih waktu yang lain.', 'warning');
                return;
            }

            const formData = new FormData(bookingForm);
            const data = {
                service_id: formData.get('service_id'),
                booking_date: formData.get('booking_date'),
                booking_time: formData.get('booking_time'),
                needs_removal: formData.get('needs_removal') || '0',
                notes: formData.get('notes') || '',
            };

            try {
                const response = await fetch('/api/bookings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                    },
                    credentials: 'include',
                    body: JSON.stringify(data),
                });

                const result = await response.json();

                if (response.ok) {
                    currentBookingId = result.booking.id;
                    snapToken = result.snap_token;
                    
                    snap.pay(snapToken, {
                        onSuccess: function(result) {
                            console.log('[v0] Payment success:', result);
                            successModal.classList.remove('hidden');
                        },
                        onPending: function(result) {
                            console.log('[v0] Payment pending:', result);
                            showToast('Pembayaran sedang diproses. Silakan tunggu...', 'info');
                        },
                        onError: function(result) {
                            console.log('[v0] Payment error:', result);
                            showToast('Pembayaran gagal. Silakan coba lagi.', 'error');
                        },
                        onClose: function() {
                            console.log('[v0] Payment dialog closed');
                        }
                    });
                } else {
                    const errorMessage = result.message || 'Failed to create booking';
                    const errorDetails = result.errors ? ': ' + Object.values(result.errors).flat().join(', ') : '';
                    showToast('Error: ' + errorMessage + errorDetails, 'error', 5000);
                    console.error('[v0] Booking error:', result);
                }
            } catch (error) {
                console.error('[v0] Error:', error);
                showToast('Error creating booking: ' + error.message, 'error');
            }
        });

        backToDashboardBtn.addEventListener('click', () => {
            window.location.href = '{{ route("dashboard") }}';
        });

        // Auto-fill from booking intent if available
        if (bookingIntent && bookingIntent.service_id) {
            // Find and select the service
            const serviceCard = document.querySelector(`.service-card[data-service-id="${bookingIntent.service_id}"]`);
            if (serviceCard) {
                const serviceType = serviceCard.dataset.type;
                const duration = parseInt(serviceCard.dataset.duration);
                const enableRemoval = parseInt(serviceCard.dataset.enableRemoval);
                selectService(bookingIntent.service_id, serviceType, duration, enableRemoval);
            }
            
            // Set date if provided
            if (bookingIntent.date) {
                dateInput.value = bookingIntent.date;
            }

            // Apply removal option if provided
            if (typeof bookingIntent.needs_removal !== 'undefined') {
                const removalRadio = document.querySelector(`input[name="needs_removal"][value="${bookingIntent.needs_removal}"]`);
                if (removalRadio) {
                    removalRadio.checked = true;
                }
            }
            
            // Load available times and auto-select time after a brief delay
            if (bookingIntent.date && bookingIntent.service_id) {
                setTimeout(async () => {
                    await loadAvailableTimes();
                    
                    // Ensure duration info is updated if removal is visible
                    updateDurationDisplay();

                    // Auto-select the time slot if provided
                    if (bookingIntent.time) {
                        const timeSlot = document.querySelector(`.time-slot[data-time="${bookingIntent.time}"]`);
                        if (timeSlot && !timeSlot.disabled) {
                            timeSlot.click();
                        }
                    }
                }, 500);
            }
        } else if (dateInput.value) {
            // Load times on page load if date is pre-filled (fallback)
            loadAvailableTimes();
        }
    </script>
</body>
</html>
