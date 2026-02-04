<?php
/**
 * Midtrans Webhook Diagnostic
 * Check: 1) Webhook endpoint is accessible
 * 2) Configuration is correct
 * 3) Recent payment transactions
 */

require __DIR__ . '/vendor/autoload.php';

// Load Laravel
$app = require __DIR__ . '/bootstrap/app.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Midtrans Webhook Diagnostic</title>
    <style>
        body { font-family: monospace; margin: 20px; background: #f5f5f5; }
        .section { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .ok { color: green; }
        .error { color: red; }
        .info { color: #666; }
        h2 { border-bottom: 2px solid #333; padding-bottom: 10px; }
        code { background: #eee; padding: 5px 10px; border-radius: 3px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        td:first-child { font-weight: bold; width: 200px; }
    </style>
</head>
<body>";

echo "<div class='section'>";
echo "<h2>🔍 Midtrans Webhook Diagnostic Report</h2>";
echo "<p>Generated: " . date('Y-m-d H:i:s') . "</p>";
echo "</div>";

// 1. Configuration Check
echo "<div class='section'>";
echo "<h2>1️⃣ Configuration Check</h2>";
echo "<table>";

$config = [
    'Server Key' => config('services.midtrans.server_key') ? '✓ Set' : '✗ Missing',
    'Client Key' => config('services.midtrans.client_key') ? '✓ Set' : '✗ Missing',
    'Is Production' => config('services.midtrans.is_production') ? 'YES (Production)' : 'NO (Sandbox)',
    'APP_ENV' => env('APP_ENV'),
    'APP_URL' => env('APP_URL'),
    'APP_DEBUG' => env('APP_DEBUG') ? 'true' : 'false',
];

foreach ($config as $key => $val) {
    $class = strpos($val, '✗') !== false ? 'error' : ($key === 'Is Production' && strpos($val, 'YES') !== false ? 'info' : 'ok');
    echo "<tr><td>$key:</td><td class='$class'>$val</td></tr>";
}

echo "</table>";
echo "</div>";

// 2. Webhook URL Check
echo "<div class='section'>";
echo "<h2>2️⃣ Webhook Endpoint Check</h2>";
$webhookUrl = env('APP_URL') . '/api/midtrans/webhook';
echo "<table>";
echo "<tr><td>Webhook URL:</td><td><code>$webhookUrl</code></td></tr>";
echo "<tr><td>Method:</td><td>POST</td></tr>";

// Try to check if endpoint is accessible
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhookUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'OPTIONS');
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$status = in_array($httpCode, [200, 405, 404]) ? "<span class='ok'>✓ Endpoint reachable (HTTP $httpCode)</span>" : "<span class='error'>✗ Endpoint unreachable (HTTP $httpCode)</span>";
echo "<tr><td>Endpoint Status:</td><td>$status</td></tr>";
echo "</table>";
echo "</div>";

// 3. Recent Payments Check
echo "<div class='section'>";
echo "<h2>3️⃣ Recent Payment Transactions</h2>";

try {
    $bookings = \App\Models\Booking::orderBy('created_at', 'desc')->limit(10)->get();
    
    if ($bookings->count() > 0) {
        echo "<table>";
        echo "<tr style='background: #f9f9f9; font-weight: bold;'>";
        echo "<td>ID</td><td>Order ID</td><td>Payment Status</td><td>Booking Status</td><td>Created</td>";
        echo "</tr>";
        
        foreach ($bookings as $booking) {
            echo "<tr>";
            echo "<td>" . $booking->id . "</td>";
            echo "<td><code>" . ($booking->transaction_id ?? 'N/A') . "</code></td>";
            echo "<td><span class='" . ($booking->payment_status === 'paid' ? 'ok' : 'info') . "'>" . $booking->payment_status . "</span></td>";
            echo "<td>" . $booking->status . "</td>";
            echo "<td>" . $booking->created_at->format('Y-m-d H:i') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='info'>No bookings found yet</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>Error loading bookings: " . $e->getMessage() . "</p>";
}

echo "</div>";

// 4. Midtrans SDK Version
echo "<div class='section'>";
echo "<h2>4️⃣ Midtrans SDK Information</h2>";
echo "<table>";

if (class_exists('Midtrans\\Config')) {
    echo "<tr><td>SDK Installed:</td><td class='ok'>✓ Yes</td></tr>";
    echo "<tr><td>Midtrans Library Version:</td><td>midtrans-php (check composer.lock)</td></tr>";
    echo "<tr><td>PHP Version:</td><td>" . phpversion() . "</td></tr>";
} else {
    echo "<tr><td>SDK Installed:</td><td class='error'>✗ No</td></tr>";
}

echo "</table>";
echo "</div>";

// 5. Log Status
echo "<div class='section'>";
echo "<h2>5️⃣ Recent Webhook Logs</h2>";

$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $lines = explode("\n", $logs);
    
    // Get last webhook-related entries
    $webhookLogs = [];
    foreach (array_reverse($lines) as $line) {
        if (strpos($line, 'MIDTRANS WEBHOOK') !== false || 
            strpos($line, 'Midtrans') !== false ||
            strpos($line, 'handleNotification') !== false) {
            $webhookLogs[] = $line;
            if (count($webhookLogs) >= 10) break;
        }
    }
    
    if (!empty($webhookLogs)) {
        echo "<p class='ok'>✓ Found " . count($webhookLogs) . " recent webhook logs:</p>";
        echo "<pre style='background: #f0f0f0; padding: 10px; overflow-x: auto; max-height: 300px;'>";
        foreach (array_reverse($webhookLogs) as $log) {
            echo htmlspecialchars($log) . "\n";
        }
        echo "</pre>";
    } else {
        echo "<p class='error'>✗ No webhook logs found in laravel.log</p>";
        echo "<p class='info'>Last 5 log entries:</p>";
        echo "<pre style='background: #f0f0f0; padding: 10px; overflow-x: auto; max-height: 200px;'>";
        foreach (array_slice(array_reverse($lines), 0, 5) as $line) {
            if (trim($line)) echo htmlspecialchars($line) . "\n";
        }
        echo "</pre>";
    }
} else {
    echo "<p class='error'>✗ Log file not found at $logFile</p>";
}

echo "</div>";

// 6. Action Items
echo "<div class='section'>";
echo "<h2>📋 Action Items</h2>";
echo "<ul>";
echo "<li><strong>If no webhook logs yet:</strong> Go to Midtrans Sandbox Dashboard → Settings → Notification URL → Set to: <code>" . $webhookUrl . "</code></li>";
echo "<li><strong>To test webhook:</strong> Create a test booking and complete payment in Midtrans Sandbox</li>";
echo "<li><strong>To view logs:</strong> SSH to server and run: <code>tail -f storage/logs/laravel.log | grep WEBHOOK</code></li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";
?>
