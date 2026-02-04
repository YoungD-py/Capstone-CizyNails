<?php
/**
 * Tandai Semua Notifikasi Sebagai Dibaca
 * Akses via POST: https://cizynails-booking.web.id/mark_all_read.php
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
    
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE is_read = 0");
    $stmt->execute();
    
    $count = $stmt->rowCount();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => "Marked $count notifications as read",
        'count' => $count
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    error_log('Mark All Read Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

exit;
