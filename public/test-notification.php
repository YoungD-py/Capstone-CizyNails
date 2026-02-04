<?php
/**
 * Test Notification Creator
 * Untuk manual testing notification feature
 */

$dbHost = '127.0.0.1';
$dbUser = 'cizynail_booking1';
$dbPass = 'lostamasta123';
$dbName = 'cizynail_booking1';

try {
    $conn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
    
    if (!$conn) {
        throw new Exception("Connection failed: " . mysqli_connect_error());
    }
    
    echo "<h2>🧪 Test Notification Creator</h2>";
    echo "<pre>";
    
    // Get first booking
    $result = mysqli_query($conn, "SELECT id, user_id FROM bookings LIMIT 1");
    
    if (mysqli_num_rows($result) === 0) {
        echo "❌ No bookings found in database\n";
        echo "Please create a booking first.\n";
        mysqli_close($conn);
        exit;
    }
    
    $booking = mysqli_fetch_assoc($result);
    $bookingId = $booking['id'];
    
    // Get booking user name
    $userResult = mysqli_query($conn, "SELECT name FROM users WHERE id = {$booking['user_id']} LIMIT 1");
    $user = mysqli_fetch_assoc($userResult);
    $userName = $user['name'] ?? 'Unknown';
    
    // Create test notification
    $now = date('Y-m-d H:i:s');
    $title = "Test Order - " . $userName;
    $message = "Ini adalah notifikasi test untuk debugging";
    
    $insertQuery = "INSERT INTO notifications (booking_id, type, title, message, is_read, created_at, updated_at) 
                    VALUES ($bookingId, 'payment_confirmed', '$title', '$message', 0, '$now', '$now')";
    
    if (mysqli_query($conn, $insertQuery)) {
        echo "✅ Test notification created successfully!\n";
        echo "\nDetails:\n";
        echo "- Booking ID: $bookingId\n";
        echo "- Customer: $userName\n";
        echo "- Title: $title\n";
        echo "- Type: payment_confirmed\n";
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "\nNext:\n";
        echo "1. Go to admin dashboard: https://cizynails-booking.web.id/admin/dashboard\n";
        echo "2. Click the notification bell icon\n";
        echo "3. You should see the test notification\n";
    } else {
        throw new Exception("Error inserting notification: " . mysqli_error($conn));
    }
    
    echo "</pre>";
    mysqli_close($conn);
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Error</h2>";
    echo "<pre style='color: red;'>";
    echo $e->getMessage() . "\n";
    echo "</pre>";
}
?>
