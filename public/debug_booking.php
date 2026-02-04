<?php
/**
 * Debug Booking & Payment Status
 * Akses via: https://cizynails-booking.web.id/debug_booking.php
 */

echo "<h2>🔍 Debug Booking & Payment Status</h2>";
echo "<pre>";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=cizynail_booking1;charset=utf8mb4", "cizynail_booking1", "lostamasta123");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "📊 BOOKING STATUS:\n";
    echo "═══════════════════════════════════════════\n\n";
    
    // Get all bookings
    $stmt = $pdo->query("
        SELECT 
            b.id,
            b.user_id,
            u.name as customer_name,
            b.service_id,
            s.name as service_name,
            b.booking_date,
            b.booking_time,
            b.status,
            b.payment_status,
            b.price,
            b.created_at,
            b.updated_at
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN services s ON b.service_id = s.id
        ORDER BY b.created_at DESC
        LIMIT 10
    ");
    
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total bookings: " . count($bookings) . "\n\n";
    
    foreach ($bookings as $booking) {
        echo "───────────────────────────────────────────\n";
        echo "ID: " . $booking['id'] . "\n";
        echo "Customer: " . $booking['customer_name'] . "\n";
        echo "Service: " . $booking['service_name'] . "\n";
        echo "Date: " . $booking['booking_date'] . " at " . $booking['booking_time'] . "\n";
        echo "Status: " . $booking['status'] . "\n";
        echo "Payment Status: " . $booking['payment_status'] . "\n";
        echo "Price: Rp. " . number_format($booking['price'], 0) . "\n";
        echo "Created: " . $booking['created_at'] . "\n";
        echo "Updated: " . $booking['updated_at'] . "\n";
    }
    
    echo "\n═══════════════════════════════════════════\n\n";
    
    echo "🔔 NOTIFICATION STATUS:\n";
    echo "═══════════════════════════════════════════\n\n";
    
    $stmt = $pdo->query("
        SELECT 
            n.id,
            n.booking_id,
            b.user_id,
            u.name as customer_name,
            n.type,
            n.title,
            n.is_read,
            n.created_at
        FROM notifications n
        LEFT JOIN bookings b ON n.booking_id = b.id
        LEFT JOIN users u ON b.user_id = u.id
        ORDER BY n.created_at DESC
        LIMIT 10
    ");
    
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total notifications: " . count($notifications) . "\n\n";
    
    foreach ($notifications as $notif) {
        echo "───────────────────────────────────────────\n";
        echo "ID: " . $notif['id'] . "\n";
        echo "Booking ID: " . $notif['booking_id'] . "\n";
        echo "Customer: " . $notif['customer_name'] . "\n";
        echo "Type: " . $notif['type'] . "\n";
        echo "Title: " . $notif['title'] . "\n";
        echo "Is Read: " . ($notif['is_read'] ? 'Yes' : 'No') . "\n";
        echo "Created: " . $notif['created_at'] . "\n";
    }
    
    echo "\n═══════════════════════════════════════════\n\n";
    
    echo "⚠️  DIAGNOSIS:\n\n";
    
    if (count($bookings) > 0 && count($notifications) > 0) {
        echo "✅ Booking dan Notification sudah ada\n";
        echo "   Cek apakah booking_id di notification match dengan booking.id\n\n";
    } elseif (count($bookings) > 0 && count($notifications) == 0) {
        echo "❌ MASALAH: Ada booking tapi TIDAK ADA notification!\n";
        echo "   Root cause:\n";
        echo "   1. Midtrans webhook tidak trigger\n";
        echo "   2. Payment status tidak update ke 'paid'\n";
        echo "   3. Notification::create() gagal di MidtransService\n\n";
        
        echo "   Solusi:\n";
        echo "   1. Cek Midtrans Dashboard webhook URL config\n";
        echo "   2. Lihat laravel.log untuk error messages\n";
        echo "   3. Check payment_status di booking (harus 'paid')\n\n";
    } else {
        echo "⚠️  Tidak ada booking apapun di database\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
