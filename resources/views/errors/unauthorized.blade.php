<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Not Logged In - Cizy Nails</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('img/cizyLogo.jpeg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-50">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full text-center">
            <!-- Emoji Icon -->
            <div class="text-6xl mb-6">🤔</div>

            <!-- Main Text -->
            <h1 class="text-4xl font-bold text-gray-900 mb-4">WHAT YOU TRNA DO BRUH?</h1>

            <!-- Description -->
            <p class="text-gray-600 text-lg mb-8">
                You need to log in first to access this page. Please log in to your account to continue.
            </p>

            <!-- Buttons -->
            <div class="flex flex-col gap-4">
                <a href="{{ route('login') }}" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                    🔐 Login
                </a>
                <a href="{{ route('register') }}" class="inline-block bg-gray-200 text-gray-800 px-8 py-3 rounded-lg hover:bg-gray-300 transition font-semibold">
                    ✍️ Create Account
                </a>
            </div>

            <!-- Home Link -->
            <a href="{{ route('landing') }}" class="text-pink-600 hover:text-pink-700 text-sm font-medium mt-8 block">
                ← Back to Home
            </a>

            <!-- Secondary Text -->
            <p class="text-gray-500 text-sm mt-8">
                Don't have an account? Sign up to book your appointment now!
            </p>
        </div>
    </div>
</body>
</html>
