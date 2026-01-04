<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cizy Nails - Book Your Appointment</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('img/cizyLogo.jpeg') }}">
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
            transition: all 0.2s;
        }
        .calendar-day:hover {
            background-color: #f3f4f6;
            transform: scale(1.05);
        }
        .calendar-day.other-month {
            color: #d1d5db;
        }
        .calendar-day.selected {
            background-color: #ec4899;
            color: white;
            border-color: #ec4899;
            transform: scale(1.05);
        }
        .calendar-day.today {
            background-color: #fbbf24;
            font-weight: bold;
        }
        .fade-in {
            animation: fadeIn 0.6s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .hover-lift {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body class="bg-gradient-to-b from-gray-50 to-white">
    <!-- Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('img/cizyLogo.jpeg') }}" alt="Cizy Nails Logo" class="h-12 w-12 rounded-full object-cover border-2 border-pink-500">
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-pink-600 to-rose-600 bg-clip-text text-transparent">Cizy Nails</h1>
                </div>
                <div class="flex gap-4 items-center">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-gray-700 hover:text-pink-600 font-medium transition-colors">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-700 hover:text-pink-600 font-medium transition-colors">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-pink-600 font-medium transition-colors">Login</a>
                        <a href="{{ route('register') }}" class="bg-gradient-to-r from-pink-600 to-rose-600 text-white px-6 py-2.5 rounded-full hover:from-pink-700 hover:to-rose-700 font-medium shadow-lg transition-all hover:shadow-xl">Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-pink-500 via-rose-500 to-pink-600 text-white py-24 overflow-hidden">
        <div class="absolute inset-0 bg-black opacity-10"></div>
        <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.05\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10 fade-in">
            <div class="mb-6">
                <img src="{{ asset('img/cizyLogo.jpeg') }}" alt="Cizy Nails Logo" class="h-24 w-24 rounded-full object-cover border-4 border-white shadow-2xl mx-auto mb-4">
            </div>
            <h2 class="text-5xl md:text-6xl font-bold mb-6 leading-tight">Welcome to Cizy Nails</h2>
            <p class="text-xl md:text-2xl mb-8 text-pink-100 max-w-3xl mx-auto">Experience premium nail care services with expert nail artists. Book your perfect appointment in just a few clicks!</p>
            <div class="flex gap-4 justify-center">
                <a href="#services" class="bg-white text-pink-600 px-8 py-3 rounded-full font-semibold hover:bg-pink-50 transition-all shadow-lg hover:shadow-xl">View Services</a>
                <a href="#booking" class="border-2 border-white text-white px-8 py-3 rounded-full font-semibold hover:bg-white hover:text-pink-600 transition-all">Book Now</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center fade-in">
                    <div class="w-16 h-16 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-gray-800">Quick Booking</h3>
                    <p class="text-gray-600">Book your appointment in less than 2 minutes with our easy-to-use system</p>
                </div>
                <div class="text-center fade-in" style="animation-delay: 0.2s;">
                    <div class="w-16 h-16 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-gray-800">Expert Artists</h3>
                    <p class="text-gray-600">Our certified nail artists provide professional and quality services</p>
                </div>
                <div class="text-center fade-in" style="animation-delay: 0.4s;">
                    <div class="w-16 h-16 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-gray-800">Premium Quality</h3>
                    <p class="text-gray-600">We use only high-quality products for the best results</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Services Section -->
            <div class="lg:col-span-2" id="services">
                <div class="mb-8">
                    <h3 class="text-3xl font-bold mb-3 text-gray-800">Our Services</h3>
                    <p class="text-gray-600">Choose from our wide range of premium nail care services</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @forelse($services as $service)
                        <div class="bg-white rounded-xl shadow-md p-6 hover-lift border border-gray-100">
                            <div class="flex items-start justify-between mb-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-pink-400 to-rose-400 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span class="bg-pink-50 text-pink-700 px-3 py-1 rounded-full text-sm font-medium">{{ $service->duration }} min</span>
                            </div>
                            <h4 class="text-xl font-bold text-gray-900 mb-2">{{ $service->name }}</h4>
                            <p class="text-gray-600 text-sm mb-4 leading-relaxed">{{ $service->description }}</p>
                            <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                                <span class="text-2xl font-bold bg-gradient-to-r from-pink-600 to-rose-600 bg-clip-text text-transparent">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                                <button class="text-pink-600 hover:text-pink-700 font-medium text-sm">View Details →</button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-2 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-8 text-center">
                            <svg class="w-16 h-16 text-blue-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-blue-800 text-lg font-semibold mb-2">No services available yet</p>
                            <p class="text-blue-600">Please check back soon for our amazing nail services!</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Booking Calendar Section -->
            <div class="bg-white rounded-xl shadow-xl p-6 h-fit sticky top-24 border border-gray-100" id="booking">
                <h3 class="text-2xl font-bold mb-6 text-gray-800">Book an Appointment</h3>
                
                <!-- Calendar -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4 bg-gradient-to-r from-pink-50 to-rose-50 p-3 rounded-lg">
                        <button onclick="previousMonth()" class="text-pink-600 hover:text-pink-800 font-bold text-xl transition-colors">←</button>
                        <h4 class="font-bold text-lg text-gray-800" id="monthYear"></h4>
                        <button onclick="nextMonth()" class="text-pink-600 hover:text-pink-800 font-bold text-xl transition-colors">→</button>
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
                <div class="mb-6 p-4 bg-gradient-to-br from-pink-50 to-rose-50 rounded-xl border-2 border-pink-200">
                    <p class="text-sm text-gray-600 mb-1 font-medium">Selected Date:</p>
                    <p class="text-lg font-bold text-pink-600" id="selectedDate">Please select a date</p>
                </div>

                <!-- Book Button -->
                @auth
                    <button onclick="bookAppointment()" class="w-full bg-gradient-to-r from-pink-600 to-rose-600 text-white py-3 rounded-xl hover:from-pink-700 hover:to-rose-700 transition-all font-semibold shadow-lg hover:shadow-xl">
                        Continue Booking →
                    </button>
                @else
                    <a href="{{ route('login') }}" class="block w-full bg-gradient-to-r from-pink-600 to-rose-600 text-white py-3 rounded-xl hover:from-pink-700 hover:to-rose-700 transition-all text-center font-semibold shadow-lg hover:shadow-xl">
                        Login to Book →
                    </a>
                @endauth
                
                <p class="text-center text-gray-500 text-sm mt-4">Book with confidence - Easy cancellation</p>
            </div>
        </div>
    </div>

    <!-- Why Choose Us Section -->
    <section class="bg-gradient-to-br from-pink-50 to-rose-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h3 class="text-3xl font-bold mb-3 text-gray-800">Why Choose Cizy Nails?</h3>
                <p class="text-gray-600 max-w-2xl mx-auto">We're committed to providing the best nail care experience with professional service and attention to detail</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-xl shadow-md hover-lift text-center">
                    <div class="text-4xl mb-3">💅</div>
                    <h4 class="font-bold text-lg mb-2 text-gray-800">Professional Staff</h4>
                    <p class="text-gray-600 text-sm">Highly trained and experienced nail artists</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-md hover-lift text-center">
                    <div class="text-4xl mb-3">✨</div>
                    <h4 class="font-bold text-lg mb-2 text-gray-800">Premium Products</h4>
                    <p class="text-gray-600 text-sm">Only the best quality nail care products</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-md hover-lift text-center">
                    <div class="text-4xl mb-3">🏆</div>
                    <h4 class="font-bold text-lg mb-2 text-gray-800">Award Winning</h4>
                    <p class="text-gray-600 text-sm">Recognized for excellence in service</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-md hover-lift text-center">
                    <div class="text-4xl mb-3">😊</div>
                    <h4 class="font-bold text-lg mb-2 text-gray-800">Customer Satisfaction</h4>
                    <p class="text-gray-600 text-sm">Thousands of happy customers</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <img src="{{ asset('img/cizyLogo.jpeg') }}" alt="Cizy Nails Logo" class="h-12 w-12 rounded-full object-cover border-2 border-pink-500">
                        <h3 class="text-2xl font-bold">Cizy Nails</h3>
                    </div>
                    <p class="text-gray-400 mb-4">Experience premium nail care services with expert nail artists. Your beauty is our passion.</p>
                    <div class="flex gap-4">
                        <a href="https://www.instagram.com/cizy.nails/" class="w-10 h-10 bg-pink-600 rounded-full flex items-center justify-center hover:bg-pink-700 transition-colors" aria-label="Instagram Cizy Nails">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="https://api.whatsapp.com/send?phone=6281210005103" class="w-10 h-10 bg-pink-600 rounded-full flex items-center justify-center hover:bg-pink-700 transition-colors" aria-label="WhatsApp Cizy Nails">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.004 2.003c-5.514 0-9.98 4.466-9.98 9.98 0 1.76.461 3.478 1.34 4.99L2 22l5.16-1.342a9.94 9.94 0 004.844 1.285h.002c5.514 0 9.98-4.466 9.98-9.98 0-2.666-1.04-5.172-2.928-7.06a9.93 9.93 0 00-7.054-2.9zm5.863 14.436c-.25.7-1.23 1.283-2.02 1.454-.54.116-1.24.208-3.61-.774-3.03-1.258-4.974-4.35-5.127-4.552-.15-.2-1.22-1.624-1.22-3.095 0-1.47.77-2.192 1.04-2.49.27-.3.59-.375.79-.375.2 0 .4 0 .57.01.18.01.42-.07.66.5.25.6.84 2.07.91 2.22.07.15.12.32.02.52-.1.2-.15.32-.3.49-.15.17-.32.38-.46.51-.15.13-.3.28-.13.55.17.27.76 1.24 1.63 2.01 1.12.99 2.07 1.3 2.37 1.45.3.15.47.13.64-.08.17-.21.74-.86.94-1.15.2-.29.4-.24.67-.14.27.1 1.74.82 2.04.97.3.15.5.22.57.34.07.12.07.72-.18 1.42z"/></svg>
                        </a>
                    </div>
                </div>
                <div>
                    <h4 class="font-bold text-lg mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#services" class="hover:text-pink-500 transition-colors">Services</a></li>
                        <li><a href="#booking" class="hover:text-pink-500 transition-colors">Book Now</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-pink-500 transition-colors">Login</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-pink-500 transition-colors">Register</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-lg mb-4">Contact</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-pink-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>Jl. Sunter Kemayoran No.7, RT.3/RW.5,Jakarta Utara</span>
                        </li>
                        <!-- <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-pink-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span>info@cizynails.com</span>
                        </li> -->
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-pink-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span>+62 812-1000-5103</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 text-center text-gray-400">
                <p>&copy; 2026 Cizy Nails. All rights reserved. Made with ❤️ for beautiful nails.</p>
            </div>
        </div>
    </footer>

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
