<?php
/**
 * Ambil Notifikasi - Versi Simple (Tanpa Laravel Bootstrap)
 * Akses via: https://cizynails-booking.web.id/get_notifications_simple.php
 */

// Disable error output yang bisa corrupt JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Silent error handling - jangan output apapun
    error_log("Notification API Error: $errstr in $errfile:$errline");
});

// Clean output buffer
ob_clean();
header('Content-Type: application/json; charset=utf-8');

try {
    // Koneksi database langsung
    $host = 'localhost';
    $dbname = 'cizynail_booking1';
    $username = 'cizynail_booking1';
    $password = 'lostamasta123';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Query notifications
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
    
    // Hitung unread
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
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    error_log('Notification PDO Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database connection error',
        'debug' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    error_log('Notification General Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

exit;
