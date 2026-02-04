<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Run Migrations - Cizy Nails Admin</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('img/cizyLogo.jpeg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ route('admin.dashboard') }}" class="text-2xl font-bold text-pink-600">Cizy Nails Admin</a>
            </div>
        </div>
    </nav>

    <div class="flex flex-col md:flex-row">
        <div class="flex-1 p-4 md:p-8">
            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-lg shadow-md p-8">
                    <h1 class="text-3xl font-bold mb-6 text-gray-800">Database Setup Required</h1>
                    
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                        <p class="text-blue-900">
                            The notifications table needs to be created in the database. Click the button below to run migrations.
                        </p>
                    </div>

                    <form id="migrationForm" method="POST" class="mb-6">
                        @csrf
                        <button 
                            type="button"
                            onclick="runMigration()"
                            id="migrateBtn"
                            class="bg-pink-600 hover:bg-pink-700 text-white font-bold py-2 px-6 rounded-lg transition w-full md:w-auto"
                        >
                            🔄 Run Migrations Now
                        </button>
                    </form>

                    <div id="output" class="bg-gray-50 border border-gray-300 rounded p-4 mb-6 hidden">
                        <div id="outputText" class="text-sm font-mono whitespace-pre-wrap"></div>
                    </div>

                    <div id="status" class="text-center"></div>

                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <h2 class="text-lg font-semibold mb-4">Alternative Methods:</h2>
                        <p class="text-gray-600 mb-2">If the button doesn't work, try accessing these URLs directly:</p>
                        <ul class="list-disc list-inside space-y-2">
                            <li><code class="bg-gray-100 px-2 py-1">https://cizynails-booking.web.id/run_migration.php</code></li>
                            <li><code class="bg-gray-100 px-2 py-1">https://cizynails-booking.web.id/migrate.php</code></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function runMigration() {
            const btn = document.getElementById('migrateBtn');
            const output = document.getElementById('output');
            const outputText = document.getElementById('outputText');
            const status = document.getElementById('status');

            btn.disabled = true;
            btn.textContent = '⏳ Running migrations...';
            output.classList.remove('hidden');
            outputText.textContent = 'Connecting to database and running migrations...\n';

            try {
                // Call artisan migrate via the migration script
                const response = await fetch('/migrate.php');
                const text = await response.text();
                
                outputText.textContent = text;
                
                if (response.ok) {
                    status.innerHTML = '<div class="bg-green-50 border border-green-500 text-green-900 p-4 rounded mt-4"><strong>✅ Success!</strong> Refresh the page or <a href="/admin/dashboard" class="underline">go to dashboard</a></div>';
                    btn.textContent = '✅ Done!';
                } else {
                    status.innerHTML = '<div class="bg-red-50 border border-red-500 text-red-900 p-4 rounded mt-4"><strong>⚠️ Warning:</strong> Check the output above for details</div>';
                    btn.textContent = '❌ Error - Check Output';
                }
            } catch (error) {
                outputText.textContent = 'Error: ' + error.message;
                status.innerHTML = '<div class="bg-red-50 border border-red-500 text-red-900 p-4 rounded mt-4"><strong>❌ Error:</strong> ' + error.message + '</div>';
                btn.textContent = '🔄 Try Again';
                btn.disabled = false;
            }
        }

        // Auto-run on page load (optional)
        // window.addEventListener('load', () => {
        //     document.getElementById('migrateBtn').click();
        // });
    </script>
</body>
</html>
