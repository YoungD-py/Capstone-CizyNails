<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Type - Cizy Nails Admin</title>
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
                    <span class="text-gray-600">{{ auth()->user()?->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-600 hover:text-gray-900">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

        <!-- Quick Admin Nav -->
        <div class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex gap-4 text-sm">
                <a href="{{ route('admin.dashboard') }}" class="text-gray-600 hover:text-pink-600">Dashboard</a>
                <a href="{{ route('admin.types.index') }}" class="text-pink-600 font-semibold">Types</a>
                <a href="{{ route('admin.services') }}" class="text-gray-600 hover:text-pink-600">Services</a>
                <a href="{{ route('admin.bookings') }}" class="text-gray-600 hover:text-pink-600">Bookings</a>
            </div>
        </div>

    <!-- Main Content -->
    <div class="max-w-2xl mx-auto p-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-3xl font-bold mb-6">Edit Type: {{ $type->name }}</h1>

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.types.update', $type) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Type Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Type Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $type->name) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-600" required>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="description" name="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-600">{{ old('description', $type->description) }}</textarea>
                </div>

                <!-- Staff Count -->
                <div>
                    <label for="staff_count" class="block text-sm font-medium text-gray-700 mb-2">Staff Count *</label>
                    <input type="number" id="staff_count" name="staff_count" value="{{ old('staff_count', $type->staff_count) }}" min="1" max="10" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-pink-600" required>
                    <p class="text-xs text-gray-500 mt-1">Jumlah staff yang tersedia untuk type ini. Total booking per type tidak boleh melebihi angka ini.</p>
                </div>

                <!-- Subtypes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-4">Subtypes (Optional)</label>
                    <div id="subtypes-container" class="space-y-4">
                        @foreach ($type->subtypes as $subtype)
                            <div class="subtype-item p-4 border border-gray-200 rounded-lg bg-gray-50">
                                <div class="flex justify-between items-center mb-3">
                                    <label class="text-sm font-medium text-gray-700">Subtype: {{ $subtype->name }}</label>
                                    <button type="button" onclick="removeSubtype(this)" class="text-red-600 hover:text-red-800">
                                        Remove
                                    </button>
                                </div>
                                
                                <div class="space-y-3">
                                    <input type="hidden" name="subtypes[][id]" value="{{ $subtype->id }}">
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                                        <input type="text" name="subtypes[][name]" value="{{ $subtype->name }}" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-pink-600" required>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                        <input type="text" name="subtypes[][description]" value="{{ $subtype->description }}" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-pink-600">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" onclick="addSubtype()" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        + Add Subtype
                    </button>
                </div>

                <!-- Submit Buttons -->
                <div class="flex gap-4 pt-6">
                    <button type="submit" class="flex-1 bg-pink-600 text-white px-6 py-3 rounded-lg hover:bg-pink-700 font-medium">
                        Update Type
                    </button>
                    <a href="{{ route('admin.types.index') }}" class="flex-1 bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 font-medium text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        let subtypeCount = {{ $type->subtypes->count() }};

        function addSubtype() {
            const container = document.getElementById('subtypes-container');
            const id = subtypeCount++;
            
            const html = `
                <div class="subtype-item p-4 border border-gray-200 rounded-lg bg-gray-50">
                    <div class="flex justify-between items-center mb-3">
                        <label class="text-sm font-medium text-gray-700">New Subtype #${id + 1}</label>
                        <button type="button" onclick="removeSubtype(this)" class="text-red-600 hover:text-red-800">
                            Remove
                        </button>
                    </div>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
                            <input type="text" name="subtypes[${id}][name]" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-pink-600" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <input type="text" name="subtypes[${id}][description]" class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-pink-600">
                        </div>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', html);
        }

        function removeSubtype(button) {
            button.closest('.subtype-item').remove();
        }
    </script>
</body>
</html>
