<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cizy Nails - Book Your Appointment</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
        }
        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            cursor: pointer;
            font-size: 0.875rem;
        }
        .calendar-day:hover {
            background-color: #f3f4f6;
        }
        .calendar-day.other-month {
            color: #d1d5db;
        }
        .calendar-day.selected {
            background-color: #ec4899;
            color: white;
            border-color: #ec4899;
        }
        .calendar-day.today {
            background-color: #fbbf24;
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-pink-600">Cizy Nails</h1>
                </div>
                <div class="flex gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-gray-900">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900">Login</a>
                        <a href="{{ route('register') }}" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700">Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-pink-500 to-rose-500 text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl font-bold mb-4">Welcome to Cizy Nails</h2>
            <p class="text-xl mb-8">Book your perfect nail appointment in just a few clicks</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Services Section -->
            <div class="lg:col-span-2">
                <h3 class="text-2xl font-bold mb-6">Our Services</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @forelse($services as $service)
                        <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ $service->name }}</h4>
                            <p class="text-gray-600 text-sm mb-4">{{ $service->description }}</p>
                            <div class="flex justify-between items-center">
                                <span class="text-pink-600 font-bold">${{ number_format($service->price, 2) }}</span>
                                <span class="text-gray-500 text-sm">{{ $service->duration }} min</span>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 bg-blue-50 border border-blue-200 rounded-lg p-6">
                            <p class="text-blue-800">No services available yet. Please check back soon!</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Booking Calendar Section -->
            <div class="bg-white rounded-lg shadow-md p-6 h-fit sticky top-4">
                <h3 class="text-xl font-bold mb-4">Book an Appointment</h3>
                
                <!-- Calendar -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <button onclick="previousMonth()" class="text-gray-600 hover:text-gray-900">←</button>
                        <h4 class="font-semibold" id="monthYear"></h4>
                        <button onclick="nextMonth()" class="text-gray-600 hover:text-gray-900">→</button>
                    </div>
                    
                    <!-- Day headers -->
                    <div class="calendar-grid mb-2">
                        <div class="text-center font-semibold text-sm text-gray-600">Sun</div>
                        <div class="text-center font-semibold text-sm text-gray-600">Mon</div>
                        <div class="text-center font-semibold text-sm text-gray-600">Tue</div>
                        <div class="text-center font-semibold text-sm text-gray-600">Wed</div>
                        <div class="text-center font-semibold text-sm text-gray-600">Thu</div>
                        <div class="text-center font-semibold text-sm text-gray-600">Fri</div>
                        <div class="text-center font-semibold text-sm text-gray-600">Sat</div>
                    </div>
                    
                    <!-- Calendar days -->
                    <div class="calendar-grid" id="calendarDays"></div>
                </div>

                <!-- Selected Date Display -->
                <div class="mb-4 p-3 bg-pink-50 rounded-lg">
                    <p class="text-sm text-gray-600">Selected Date:</p>
                    <p class="text-lg font-semibold text-pink-600" id="selectedDate">None</p>
                </div>

                <!-- Book Button -->
                @auth
                    <button onclick="bookAppointment()" class="w-full bg-pink-600 text-white py-2 rounded-lg hover:bg-pink-700 transition">
                        Continue Booking
                    </button>
                @else
                    <a href="{{ route('login') }}" class="block w-full bg-pink-600 text-white py-2 rounded-lg hover:bg-pink-700 transition text-center">
                        Login to Book
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <script>
        let currentDate = new Date();
        let selectedDate = null;

        function renderCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            
            // Update month/year display
            const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                              'July', 'August', 'September', 'October', 'November', 'December'];
            document.getElementById('monthYear').textContent = `${monthNames[month]} ${year}`;
            
            // Get first day of month and number of days
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const daysInPrevMonth = new Date(year, month, 0).getDate();
            
            const calendarDays = document.getElementById('calendarDays');
            calendarDays.innerHTML = '';
            
            // Previous month days
            for (let i = firstDay - 1; i >= 0; i--) {
                const day = document.createElement('div');
                day.className = 'calendar-day other-month';
                day.textContent = daysInPrevMonth - i;
                calendarDays.appendChild(day);
            }
            
            // Current month days
            const today = new Date();
            for (let i = 1; i <= daysInMonth; i++) {
                const day = document.createElement('div');
                day.className = 'calendar-day';
                day.textContent = i;
                
                const dateObj = new Date(year, month, i);
                
                // Highlight today
                if (dateObj.toDateString() === today.toDateString()) {
                    day.classList.add('today');
                }
                
                // Highlight selected date
                if (selectedDate && dateObj.toDateString() === selectedDate.toDateString()) {
                    day.classList.add('selected');
                }
                
                // Only allow future dates
                if (dateObj >= today) {
                    day.onclick = () => selectDate(dateObj);
                } else {
                    day.style.opacity = '0.5';
                    day.style.cursor = 'not-allowed';
                }
                
                calendarDays.appendChild(day);
            }
            
            // Next month days
            const totalCells = calendarDays.children.length;
            const remainingCells = 42 - totalCells;
            for (let i = 1; i <= remainingCells; i++) {
                const day = document.createElement('div');
                day.className = 'calendar-day other-month';
                day.textContent = i;
                calendarDays.appendChild(day);
            }
        }

        function selectDate(date) {
            selectedDate = date;
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('selectedDate').textContent = date.toLocaleDateString('en-US', options);
            renderCalendar();
        }

        function previousMonth() {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        }

        function nextMonth() {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        }

        function bookAppointment() {
            if (!selectedDate) {
                alert('Please select a date');
                return;
            }
            // Store selected date and redirect to booking page
            const dateStr = selectedDate.toISOString().split('T')[0];
            window.location.href = `/booking?date=${dateStr}`;
        }

        // Initialize calendar
        renderCalendar();
    </script>
</body>
</html>
