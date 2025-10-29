<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services - Cizy Nails Admin</title>
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
                <a href="{{ route('admin.services') }}" class="block px-4 py-2 rounded-lg bg-pink-100 text-pink-600">
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
                <h1 class="text-3xl font-bold">Manage Services</h1>
                <button onclick="openAddServiceModal()" class="bg-pink-600 text-white px-6 py-2 rounded-lg hover:bg-pink-700">
                    Add Service
                </button>
            </div>

            <!-- Services Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($services as $service)
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $service->name }}</h3>
                        <p class="text-gray-600 text-sm mb-4">{{ $service->description }}</p>
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-pink-600 font-bold">${{ number_format($service->price, 2) }}</span>
                            <span class="text-gray-500 text-sm">{{ $service->duration }} min</span>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="editService({{ $service->id }})" class="flex-1 px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                                Edit
                            </button>
                            <button onclick="deleteService({{ $service->id }})" class="flex-1 px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
                                Delete
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                        <p class="text-blue-800">No services yet. Create your first service!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Add/Edit Service Modal -->
    <div id="serviceModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-md w-full">
            <h2 class="text-2xl font-bold mb-6">Add Service</h2>
            <form id="serviceForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Service Name</label>
                    <input type="text" id="serviceName" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Description</label>
                    <textarea id="serviceDescription" name="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Duration (minutes)</label>
                    <input type="number" id="serviceDuration" name="duration" min="15" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Price</label>
                    <input type="number" id="servicePrice" name="price" step="0.01" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-pink-600 text-white py-2 rounded-lg hover:bg-pink-700">Save</button>
                    <button type="button" onclick="closeServiceModal()" class="flex-1 bg-gray-300 text-gray-900 py-2 rounded-lg hover:bg-gray-400">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddServiceModal() {
            document.getElementById('serviceModal').classList.remove('hidden');
            document.getElementById('serviceForm').reset();
        }

        function closeServiceModal() {
            document.getElementById('serviceModal').classList.add('hidden');
        }

        function editService(serviceId) {
            // TODO: Implement edit functionality
            alert('Edit functionality coming soon');
        }

        function deleteService(serviceId) {
            if (confirm('Are you sure you want to delete this service?')) {
                fetch(`/api/services/${serviceId}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    alert('Service deleted successfully');
                    location.reload();
                })
                .catch(error => {
                    alert('Error deleting service');
                    console.error(error);
                });
            }
        }

        document.getElementById('serviceForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);

            try {
                const response = await fetch('/api/services', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data),
                });

                const result = await response.json();

                if (response.ok) {
                    alert('Service created successfully');
                    closeServiceModal();
                    location.reload();
                } else {
                    alert('Error: ' + (result.message || 'Failed to create service'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error creating service');
            }
        });
    </script>
</body>
</html>
