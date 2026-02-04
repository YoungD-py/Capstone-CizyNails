<?php
/**
 * Manual Webhook Test - Simulates Midtrans webhook notification
 * Access: https://cizynails-booking.web.id/test_webhook.php
 */

// Manually test webhook by posting data that looks like Midtrans notification
$testNotification = [
    'transaction_id' => '4ec61e11-5ecc-4621-8d56-05bb1e5551b5',
    'order_id' => 'ORDER-' . rand(10000, 99999),
    'payment_type' => 'credit_card',
    'transaction_status' => 'settlement',
    'fraud_status' => 'accept',
    'transaction_time' => date('Y-m-d H:i:s'),
    'settlement_time' => date('Y-m-d H:i:s'),
    'status_code' => '200',
    'signature_key' => 'test_signature_key'
];

// Get database connection
try {
    $conn = mysqli_connect("127.0.0.1", "cizynail_user", "X#kL9mN@pQw2vY5zB", "cizynail_booking");
    if (!$conn) throw new Exception("DB Connection Failed");
    
    // Log incoming webhook
    error_log("\n\n" . str_repeat("=", 80));
    error_log("WEBHOOK TEST INITIATED AT: " . date('Y-m-d H:i:s'));
    error_log("Test Notification: " . json_encode($testNotification, JSON_PRETTY_PRINT));
    error_log(str_repeat("=", 80));
    
    // Try to load Laravel and call the handler
    require __DIR__ . '/bootstrap/app.php';
    
    $app = require __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    // Simulate the request
    $request = Illuminate\Http\Request::create(
        '/api/midtrans/webhook',
        'POST',
        $testNotification,
        [],
        [],
        ['HTTP_X-MIDTRANS-SIGNATURE' => 'test_signature']
    );
    
    $response = $kernel->handle($request);
    
    echo "<pre>";
    echo "✅ Webhook Test Completed\n";
    echo "Response Status: " . $response->status() . "\n";
    echo "Response Content: " . $response->content() . "\n";
    echo "\n🔍 Check laravel.log for webhook processing logs...\n";
    echo "</pre>";
    
    // Check if webhook was logged
    $logFile = __DIR__ . '/storage/logs/laravel.log';
    if (file_exists($logFile)) {
        $logs = file_get_contents($logFile);
        if (strpos($logs, 'MIDTRANS WEBHOOK RECEIVED') !== false) {
            echo "<p style='color: green;'><strong>✓ Webhook log found in laravel.log</strong></p>";
        } else {
            echo "<p style='color: red;'><strong>✗ Webhook log NOT found - check handler code</strong></p>";
        }
    }
    
} catch (Exception $e) {
    error_log("WEBHOOK TEST ERROR: " . $e->getMessage());
    echo "<pre>";
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    echo "</pre>";
}
?>
