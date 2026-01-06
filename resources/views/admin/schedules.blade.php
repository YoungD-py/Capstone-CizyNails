<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Schedules - Cizy Nails Admin</title>
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
                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100">
                    Dashboard
                </a>
                <a href="{{ route('admin.bookings') }}" class="block px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100">
                    Bookings
                </a>
                   <a href="{{ route('admin.types.index') }}" class="block px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100">
                       Types
                   </a>
                   <a href="{{ route('admin.services') }}" class="block px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100">
                       Services
                   </a>
                <a href="{{ route('admin.schedules') }}" class="block px-4 py-2 rounded-lg bg-pink-100 text-pink-600">
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
                <h1 class="text-3xl font-bold">Daily Time Slots & Capacity</h1>
                <div class="text-sm text-gray-600">
                    <p>Time slots are auto-generated when customers view available times</p>
                    <p class="text-xs">Capacity: Nails Art (2 slots) | Eyelash (1 slot) per time</p>
                </div>
            </div>

            <!-- Schedules by Date -->
            <div class="space-y-6">
                @forelse($schedules as $date => $timeSlots)
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-bold mb-4">{{ \Carbon\Carbon::parse($date)->format('l, M d, Y') }}</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Time Slot</th>
                                        <th class="px-4 py-2 text-center">Nails Art Booked</th>
                                        <th class="px-4 py-2 text-center">Eyelash Booked</th>
                                        <th class="px-4 py-2 text-center">Nails Art Available</th>
                                        <th class="px-4 py-2 text-center">Eyelash Available</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @foreach($timeSlots as $slot)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 font-semibold">{{ \Carbon\Carbon::parse($slot->time_slot)->format('H:i') }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $slot->nails_art_booked >= 2 ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                                    {{ $slot->nails_art_booked }}/2
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $slot->eyelash_booked >= 1 ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                                    {{ $slot->eyelash_booked }}/1
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="px-2 py-1 rounded text-xs {{ $slot->nails_art_booked < 2 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                                    {{ $slot->nails_art_booked < 2 ? 'Yes' : 'Full' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="px-2 py-1 rounded text-xs {{ $slot->eyelash_booked < 1 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                                    {{ $slot->eyelash_booked < 1 ? 'Yes' : 'Full' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                        <p class="text-blue-800 mb-2">No time slots created yet for the next 7 days.</p>
                        <p class="text-blue-600 text-sm">Time slots will be automatically generated when customers select a date for booking.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</body>
</html>
