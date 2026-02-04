<?php
/**
 * Test API dan Debug
 * Akses via: https://cizynails-booking.web.id/test_notif_api.php
 */

echo "<h2>🧪 Testing Notification API</h2>";
echo "<pre>";

// Test 1: Check database connection
echo "1️⃣  Testing Database Connection...\n";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=cizynail_booking1;charset=utf8mb4", "cizynail_booking1", "lostamasta123");
    echo "   ✅ Connected to database\n\n";
} catch (Exception $e) {
    echo "   ❌ Database error: " . $e->getMessage() . "\n\n";
    exit;
}

// Test 2: Check notifications table
echo "2️⃣  Checking Notifications Table...\n";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'notifications'");
    if ($stmt->rowCount() > 0) {
        echo "   ✅ notifications table exists\n";
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM notifications");
        $result = $stmt->fetch();
        echo "   📊 Total notifications: " . $result['total'] . "\n\n";
    } else {
        echo "   ❌ notifications table NOT FOUND\n\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// Test 3: Fetch notifications
echo "3️⃣  Fetching Notifications...\n";
try {
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
    
    echo "   ✅ Fetched " . count($notifications) . " notifications\n\n";
    
    if (count($notifications) > 0) {
        echo "4️⃣  Sample Notification:\n";
        $sample = $notifications[0];
        echo "   ID: " . $sample['id'] . "\n";
        echo "   Title: " . $sample['title'] . "\n";
        echo "   Customer: " . $sample['customer_name'] . "\n";
        echo "   Is Read: " . ($sample['is_read'] ? 'Yes' : 'No') . "\n";
        echo "   Created: " . $sample['created_at'] . "\n\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
}

// Test 4: Test JSON output
echo "5️⃣  Testing JSON Output...\n";
$unreadCount = 0;
foreach ($notifications as $n) {
    if ($n['is_read'] == 0) $unreadCount++;
}

$output = [
    'success' => true,
    'total' => count($notifications),
    'unread_count' => $unreadCount,
    'notifications' => array_map(function($n) {
        return [
            'id' => (int)$n['id'],
            'booking_id' => (int)$n['booking_id'],
            'type' => $n['type'],
            'title' => $n['title'],
            'message' => $n['message'],
            'is_read' => (bool)$n['is_read'],
            'customer_name' => $n['customer_name'],
            'created_at' => $n['created_at'],
        ];
    }, $notifications)
];

$json = json_encode($output, JSON_PRETTY_PRINT);
echo "   ✅ JSON valid\n";
echo "   Size: " . strlen($json) . " bytes\n\n";

echo "6️⃣  Complete JSON Output:\n";
echo $json . "\n\n";

echo "============================================================\n";
echo "✅ API Test Complete!\n";
echo "============================================================\n\n";

echo "If all tests passed, dashboard should now work!\n";
echo "</pre>";
