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

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Services Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($services as $service)
                    <div class="bg-white rounded-lg shadow-md p-6 {{ !$service->is_active ? 'opacity-60' : '' }}">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $service->name }}</h3>
                            @if(!$service->is_active)
                                <span class="px-2 py-1 bg-gray-200 text-gray-600 text-xs rounded">Inactive</span>
                            @endif
                        </div>
                        <p class="text-gray-600 text-sm mb-2">{{ $service->description ?? 'No description' }}</p>
                        <div class="mb-2">
                            <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">
                                {{ ucfirst(str_replace('_', ' ', $service->type)) }}
                            </span>
                            @if($service->subtype)
                                <span class="inline-block px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded ml-1">
                                    {{ ucfirst($service->subtype) }}
                                </span>
                            @endif
                        </div>
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-pink-600 font-bold">Rp {{ number_format($service->price, 0) }}</span>
                            <span class="text-gray-500 text-sm">{{ $service->duration_minutes }} min</span>
                        </div>
                        <div class="flex gap-2">
                            <button onclick='editService(@json($service))' class="flex-1 px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                                Edit
                            </button>
                            <form action="{{ route('admin.services.destroy', $service->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Are you sure you want to delete this service?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
                                    Delete
                                </button>
                            </form>
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
        <div class="bg-white rounded-lg p-8 max-w-md w-full max-h-[90vh] overflow-y-auto">
            <h2 id="modalTitle" class="text-2xl font-bold mb-6">Add Service</h2>
            <form id="serviceForm" method="POST" action="{{ route('admin.services.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" id="serviceId" name="_method" value="POST">
                
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Service Name *</label>
                    <input type="text" id="serviceName" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Type *</label>
                    <select id="serviceType" name="type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500" onchange="toggleSubtype()">
                        <option value="">Select Type</option>
                        <option value="nails_art">Nails Art</option>
                        <option value="eyelash">Eyelash</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div id="subtypeContainer" class="hidden">
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Subtype</label>
                    <select id="serviceSubtype" name="subtype" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                        <option value="">None</option>
                        <option value="natural">Natural (Kuku asli)</option>
                        <option value="extension">Extension</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Description</label>
                    <textarea id="serviceDescription" name="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Duration (minutes) *</label>
                    <input type="number" id="serviceDuration" name="duration_minutes" min="15" max="480" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Staff Count *</label>
                    <input type="number" id="serviceStaffCount" name="staff_count" min="1" max="10" value="1" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-900 mb-2">Price (Rp) *</label>
                    <input type="number" id="servicePrice" name="price" step="1000" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500">
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" id="serviceIsActive" name="is_active" value="1" checked class="mr-2">
                        <span class="text-sm font-semibold text-gray-900">Active</span>
                    </label>
                </div>
                
                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-pink-600 text-white py-2 rounded-lg hover:bg-pink-700">Save</button>
                    <button type="button" onclick="closeServiceModal()" class="flex-1 bg-gray-300 text-gray-900 py-2 rounded-lg hover:bg-gray-400">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleSubtype() {
            const type = document.getElementById('serviceType').value;
            const subtypeContainer = document.getElementById('subtypeContainer');
            if (type === 'nails_art') {
                subtypeContainer.classList.remove('hidden');
            } else {
                subtypeContainer.classList.add('hidden');
                document.getElementById('serviceSubtype').value = '';
            }
        }

        function openAddServiceModal() {
            document.getElementById('modalTitle').textContent = 'Add Service';
            document.getElementById('serviceForm').action = '{{ route("admin.services.store") }}';
            document.getElementById('serviceId').value = 'POST';
            document.getElementById('serviceForm').reset();
            document.getElementById('serviceIsActive').checked = true;
            document.getElementById('subtypeContainer').classList.add('hidden');
            document.getElementById('serviceModal').classList.remove('hidden');
        }

        function closeServiceModal() {
            document.getElementById('serviceModal').classList.add('hidden');
        }

        function editService(service) {
            document.getElementById('modalTitle').textContent = 'Edit Service';
            document.getElementById('serviceForm').action = `/admin/services/${service.id}`;
            document.getElementById('serviceId').value = 'PUT';
            
            document.getElementById('serviceName').value = service.name;
            document.getElementById('serviceType').value = service.type;
            document.getElementById('serviceSubtype').value = service.subtype || '';
            document.getElementById('serviceDescription').value = service.description || '';
            document.getElementById('serviceDuration').value = service.duration_minutes;
            document.getElementById('serviceStaffCount').value = service.staff_count || 1;
            document.getElementById('servicePrice').value = service.price;
            document.getElementById('serviceIsActive').checked = service.is_active;
            
            toggleSubtype();
            document.getElementById('serviceModal').classList.remove('hidden');
        }
    </script>
</body>
</html>
