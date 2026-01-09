<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Not Authorized - Cizy Nails</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('img/cizyLogo.jpeg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-pink-50 to-rose-50">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full text-center">
            <!-- Emoji Icon -->
            <div class="text-6xl mb-6">🚫</div>

            <!-- Main Text -->
            <h1 class="text-4xl font-bold text-gray-900 mb-4">SORRY THIS IS NOT YOUR ACCESS</h1>

            <!-- Description -->
            <p class="text-gray-600 text-lg mb-8">
                You don't have permission to access this page. This area is restricted to a different user role.
            </p>

            <!-- Button -->
            <a href="{{ route('landing') }}" class="inline-block bg-pink-600 text-white px-8 py-3 rounded-lg hover:bg-pink-700 transition font-semibold">
                ← Go Back Home
            </a>

            <!-- Secondary Text -->
            <p class="text-gray-500 text-sm mt-8">
                If you believe this is an error, please contact support.
            </p>
        </div>
    </div>
</body>
</html>
