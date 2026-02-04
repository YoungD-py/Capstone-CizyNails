<?php
/**
 * Auto Uploader - Upload file notifikasi ke production
 * Upload file ini dulu ke public_html/, lalu akses via browser
 */

echo "<h2>📤 Auto Uploader - Notification System</h2>";
echo "<pre>";

// File yang akan di-create di production
$files = [
    'check_structure.php' => base64_decode('<?php
/**
 * Cek Struktur Direktori Production
 * Akses via: https://cizynails-booking.web.id/check_structure.php
 */

echo "<h2>🔍 Checking Production Directory Structure</h2>";
echo "<pre>";

echo "📂 Current Directory: " . __DIR__ . "\n";
echo "📂 Real Path: " . realpath(__DIR__) . "\n\n";

echo "============================================================\n";
echo "Checking Laravel Files:\n";
echo "============================================================\n\n";

// Check possible locations
$possiblePaths = [
    \'vendor/autoload.php\' => __DIR__ . \'/../vendor/autoload.php\',
    \'bootstrap/app.php\' => __DIR__ . \'/../bootstrap/app.php\',
    \'app/Models/Notification.php\' => __DIR__ . \'/../app/Models/Notification.php\',
    \'routes/api.php\' => __DIR__ . \'/../routes/api.php\',
    \'.env file\' => __DIR__ . \'/../.env\',
];

foreach ($possiblePaths as $name => $path) {
    if (file_exists($path)) {
        echo "✅ FOUND: $name\n";
        echo "   Path: " . realpath($path) . "\n\n";
    } else {
        echo "❌ NOT FOUND: $name\n";
        echo "   Checked: $path\n\n";
    }
}

echo "============================================================\n";
echo "Parent Directory Contents:\n";
echo "============================================================\n\n";

$parentDir = dirname(__DIR__);
if (is_dir($parentDir)) {
    $items = scandir($parentDir);
    foreach ($items as $item) {
        if ($item == \'.\' || $item == \'..\') continue;
        $fullPath = $parentDir . \'/\' . $item;
        $type = is_dir($fullPath) ? \'📁 DIR \' : \'📄 FILE\';
        echo "$type  $item\n";
    }
}

echo "\n============================================================\n";
echo "Database Config Check:\n";
echo "============================================================\n\n";

$envPath = __DIR__ . \'/../.env\';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    preg_match(\'/DB_DATABASE=(.+)/\', $envContent, $dbName);
    preg_match(\'/DB_USERNAME=(.+)/\', $envContent, $dbUser);
    if ($dbName) echo "✅ DB_DATABASE: " . trim($dbName[1]) . "\n";
    if ($dbUser) echo "✅ DB_USERNAME: " . trim($dbUser[1]) . "\n";
}

echo "</pre>";'),

    'get_notifications_simple.php' => '<?php
/**
 * Ambil Notifikasi - Versi Simple (Tanpa Laravel Bootstrap)
 */

header(\'Content-Type: application/json\');

try {
    $host = \'localhost\';
    $dbname = \'cizynail_booking1\';
    $username = \'cizynail_booking1\';
    $password = \'Cizy0ct0pu5\';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("
        SELECT 
            n.id, n.booking_id, n.type, n.title, n.message, n.is_read, n.created_at,
            u.name as customer_name
        FROM notifications n
        LEFT JOIN bookings b ON n.booking_id = b.id
        LEFT JOIN users u ON b.user_id = u.id
        ORDER BY n.created_at DESC
    ");
    
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $unreadCount = 0;
    foreach ($notifications as $notif) {
        if ($notif[\'is_read\'] == 0) $unreadCount++;
    }
    
    $formatted = array_map(function($n) {
        return [
            \'id\' => (int)$n[\'id\'],
            \'booking_id\' => (int)$n[\'booking_id\'],
            \'type\' => $n[\'type\'],
            \'title\' => $n[\'title\'],
            \'message\' => $n[\'message\'],
            \'is_read\' => (bool)$n[\'is_read\'],
            \'customer_name\' => $n[\'customer_name\'] ?? \'Unknown\',
            \'created_at\' => date(\'Y-m-d H:i:s\', strtotime($n[\'created_at\'])),
        ];
    }, $notifications);
    
    echo json_encode([
        \'success\' => true,
        \'total\' => count($notifications),
        \'unread_count\' => $unreadCount,
        \'notifications\' => $formatted
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([\'success\' => false, \'error\' => $e->getMessage()], JSON_PRETTY_PRINT);
}',

    'mark_notification_read.php' => '<?php
header(\'Content-Type: application/json\');
try {
    $pdo = new PDO("mysql:host=localhost;dbname=cizynail_booking1;charset=utf8mb4", "cizynail_booking1", "Cizy0ct0pu5");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $id = isset($_GET[\'id\']) ? (int)$_GET[\'id\'] : 0;
    if ($id <= 0) throw new Exception(\'Invalid notification ID\');
    
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode([\'success\' => true, \'message\' => \'Marked as read\']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([\'success\' => false, \'error\' => $e->getMessage()]);
}',

    'mark_all_read.php' => '<?php
header(\'Content-Type: application/json\');
try {
    $pdo = new PDO("mysql:host=localhost;dbname=cizynail_booking1;charset=utf8mb4", "cizynail_booking1", "Cizy0ct0pu5");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE is_read = 0");
    $stmt->execute();
    
    echo json_encode([\'success\' => true, \'message\' => \'All marked as read\']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([\'success\' => false, \'error\' => $e->getMessage()]);
}'
];

// Create files
foreach ($files as $filename => $content) {
    $result = file_put_contents($filename, $content);
    if ($result !== false) {
        echo "✅ Created: $filename (" . strlen($content) . " bytes)\n";
    } else {
        echo "❌ Failed: $filename\n";
    }
}

echo "\n============================================================\n";
echo "✅ SEMUA FILE NOTIFICATION BERHASIL DI-CREATE!\n";
echo "============================================================\n\n";

echo "Test sekarang:\n";
echo "1. https://cizynails-booking.web.id/check_structure.php\n";
echo "2. https://cizynails-booking.web.id/get_notifications_simple.php\n";
echo "3. Dashboard admin\n\n";

echo "⚠️  JANGAN LUPA upload dashboard.blade.php juga!\n";
echo "   Location: Cizy-Nails-Project/resources/views/admin/dashboard.blade.php\n";

echo "</pre>";
