<?php
/**
 * Notification System Auto-Creator (Simple Version)
 * This script creates all notification files in public_html/
 * Access: https://cizynails-booking.web.id/setup_notif.php
 */

echo "<h2>✅ Creating Notification System Files...</h2>";
echo "<pre>";

// 1. check_structure.php
$file1 = 'check_structure.php';
$content1 = <<<'EOF'
<?php
echo "<h2>🔍 Checking Production Directory Structure</h2>";
echo "<pre>";
echo "📂 Current Directory: " . __DIR__ . "\n";
echo "📂 Real Path: " . realpath(__DIR__) . "\n\n";

// Check files
$checks = [
    'vendor/autoload.php' => __DIR__ . '/../vendor/autoload.php',
    'bootstrap/app.php' => __DIR__ . '/../bootstrap/app.php',
    'app/Models/Notification.php' => __DIR__ . '/../app/Models/Notification.php',
    'routes/api.php' => __DIR__ . '/../routes/api.php',
    '.env file' => __DIR__ . '/../.env',
];

foreach ($checks as $name => $path) {
    if (file_exists($path)) {
        echo "✅ FOUND: $name\n";
    } else {
        echo "❌ NOT FOUND: $name\n";
    }
}

echo "\n📂 Parent directory contents:\n";
$parentDir = dirname(__DIR__);
if (is_dir($parentDir)) {
    $items = @scandir($parentDir);
    if ($items) {
        foreach ($items as $item) {
            if ($item != '.' && $item != '..') {
                echo "   $item\n";
            }
        }
    }
}

echo "</pre>";
EOF;

if (file_put_contents($file1, $content1)) {
    echo "✅ Created: $file1\n";
} else {
    echo "❌ Failed: $file1\n";
}

// 2. get_notifications_simple.php
$file2 = 'get_notifications_simple.php';
$content2 = <<<'EOF'
<?php
header('Content-Type: application/json');

try {
    $host = 'localhost';
    $dbname = 'cizynail_booking1';
    $username = 'cizynail_booking1';
    $password = 'lostamasta123';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("
        SELECT 
            n.id,
            n.booking_id,
            n.type,
            n.title,
            n.message,
            n.is_read,
            n.created_at,
            COALESCE(u.name, 'Unknown') as customer_name
        FROM notifications n
        LEFT JOIN bookings b ON n.booking_id = b.id
        LEFT JOIN users u ON b.user_id = u.id
        ORDER BY n.created_at DESC
    ");
    
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $unreadCount = 0;
    $formatted = [];
    
    foreach ($notifications as $n) {
        if ($n['is_read'] == 0) {
            $unreadCount++;
        }
        
        $formatted[] = [
            'id' => (int)$n['id'],
            'booking_id' => (int)$n['booking_id'],
            'type' => $n['type'],
            'title' => $n['title'],
            'message' => $n['message'],
            'is_read' => (bool)$n['is_read'],
            'customer_name' => $n['customer_name'],
            'created_at' => $n['created_at'],
        ];
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'total' => count($notifications),
        'unread_count' => $unreadCount,
        'notifications' => $formatted
    ], JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
EOF;

if (file_put_contents($file2, $content2)) {
    echo "✅ Created: $file2\n";
} else {
    echo "❌ Failed: $file2\n";
}

echo "\n============================================================\n";
echo "✅ Notification files created successfully!\n";
echo "============================================================\n\n";

echo "Next steps:\n";
echo "1. Test API: https://cizynails-booking.web.id/get_notifications_simple.php\n";
echo "2. Check structure: https://cizynails-booking.web.id/check_structure.php\n";
echo "3. Reload dashboard admin\n\n";

echo "</pre>";
