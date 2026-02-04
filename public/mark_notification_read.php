<?php
/**
 * Tandai Notifikasi Sebagai Dibaca
 * Akses via POST: https://cizynails-booking.web.id/mark_notification_read.php?id=X
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);
ob_clean();
header('Content-Type: application/json; charset=utf-8');

try {
    $host = 'localhost';
    $dbname = 'cizynail_booking1';
    $username = 'cizynail_booking1';
    $password = 'lostamasta123';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        throw new Exception('Invalid notification ID');
    }
    
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    $stmt->execute([$id]);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Notification marked as read',
        'id' => $id
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    error_log('Mark Read Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

exit;
