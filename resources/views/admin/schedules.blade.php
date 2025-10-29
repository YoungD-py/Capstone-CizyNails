<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Schedules - Cizy Nails Admin</title>
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
                <h1 class="text-3xl font-bold">Manage Schedules</h1>
                <button onclick="openAddScheduleModal()" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">
                    Add Schedule
                </button>
            </div>

            <!-- Schedules by Service -->
            <div class="space-y-6">
                @forelse($services as $service)
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-xl font-bold mb-4">{{ $service->name }}</h2>
                        @if($service->schedules->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left">Day</th>
                                            <th class="px-4 py-2 text-left">Start Time</th>
                                            <th class="px-4 py-2 text-left">End Time</th>
                                            <th class="px-4 py-2 text-left">Available</th>
                                            <th class="px-4 py-2 text-left">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        @foreach($service->schedules as $schedule)
                                            <tr>
                                                <td class="px-4 py-2">{{ ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][$schedule->day_of_week] }}</td>
                                                <td class="px-4 py-2">{{ $schedule->start_time }}</td>
                                                <td class="px-4 py-2">{{ $schedule->end_time }}</td>
                                                <td class="px-4 py-2">
                                                    <span class="px-2 py-1 rounded text-sm {{ $schedule->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                        {{ $schedule->is_available ? 'Yes' : 'No' }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <button onclick="deleteSchedule({{ $schedule->id }})" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-500 text-sm">No schedules set for this service</p>
                        @endif
                    </div>
                @empty
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                        <p class="text-blue-800">No services available. Create services first!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Add Schedule Modal -->
    <div id="scheduleModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h2 class="text-2xl font-bold mb-6">Add Schedule</h2>
            <form id="scheduleForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Service</label>
                    <select id="scheduleService" name="service_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                        <option value="">Select a service...</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Day of Week</label>
                    <select id="scheduleDay" name="day_of_week" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                        <option value="">Select a day...</option>
                        <option value="0">Sunday</option>
                        <option value="1">Monday</option>
                        <option value="2">Tuesday</option>
                        <option value="3">Wednesday</option>
                        <option value="4">Thursday</option>
                        <option value="5">Friday</option>
                        <option value="6">Saturday</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Start Time</label>
                    <input type="time" id="scheduleStart" name="start_time" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">End Time</label>
                    <input type="time" id="scheduleEnd" name="end_time" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" id="scheduleAvailable" name="is_available" checked class="w-4 h-4 text-pink-600">
                    <label for="scheduleAvailable" class="ml-2 text-sm text-gray-900">Available</label>
                </div>
                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-pink-600 text-white py-2 rounded-lg hover:bg-pink-700">Save</button>
                    <button type="button" onclick="closeScheduleModal()" class="flex-1 bg-gray-300 text-gray-900 py-2 rounded-lg hover:bg-gray-400">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddScheduleModal() {
            document.getElementById('scheduleModal').classList.remove('hidden');
            document.getElementById('scheduleForm').reset();
        }

        function closeScheduleModal() {
            document.getElementById('scheduleModal').classList.add('hidden');
        }

        function deleteSchedule(scheduleId) {
            if (confirm('Are you sure you want to delete this schedule?')) {
                fetch(`/api/schedules/${scheduleId}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    alert('Schedule deleted successfully');
                    location.reload();
                })
                .catch(error => {
                    alert('Error deleting schedule');
                    console.error(error);
                });
            }
        }

        document.getElementById('scheduleForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);
            data.is_available = data.is_available ? true : false;

            try {
                const response = await fetch('/api/schedules', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data),
                });

                const result = await response.json();

                if (response.ok) {
                    alert('Schedule created successfully');
                    closeScheduleModal();
                    location.reload();
                } else {
                    alert('Error: ' + (result.message || 'Failed to create schedule'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error creating schedule');
            }
        });
    </script>
</body>
</html>
