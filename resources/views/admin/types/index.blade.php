<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Types - Cizy Nails Admin</title>
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
                <a href="{{ route('admin.bookings') }}" class="block px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100">
                    Bookings
                </a>
                <a href="{{ route('admin.types.index') }}" class="block px-4 py-2 rounded-lg bg-pink-100 text-pink-600">
                    Types
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
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold">Manage Types</h1>
                    <a href="{{ route('admin.types.create') }}" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700">
                        + Add Type
                    </a>
                </div>

                @if (session('success'))
                    <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-100 border-b">
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Type Name</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Subtypes</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Services</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($types as $type)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <strong>{{ $type->name }}</strong>
                                        @if ($type->description)
                                            <p class="text-gray-600 text-xs mt-1">{{ $type->description }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        @if ($type->subtypes->count() > 0)
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($type->subtypes as $subtype)
                                                    <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">{{ $subtype->name }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic">No subtypes</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $type->services()->count() }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <div class="flex gap-2">
                                            <a href="{{ route('admin.types.edit', $type) }}" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('admin.types.destroy', $type) }}" class="inline" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-600">
                                        No types found. <a href="{{ route('admin.types.create') }}" class="text-pink-600 hover:underline">Create one</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
