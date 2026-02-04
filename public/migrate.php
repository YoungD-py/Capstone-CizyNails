<?php
/**
 * Simple Migration Runner - Direct SQL approach
 * No routing needed, can be accessed directly from browser
 */

// Database connection
$dbHost = '127.0.0.1';
$dbUser = 'cizynail_booking1';
$dbPass = 'lostamasta123';
$dbName = 'cizynail_booking1';

try {
    $conn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
    
    if (!$conn) {
        throw new Exception("Connection failed: " . mysqli_connect_error());
    }
    
    echo "<h2>🔧 Running Database Migration</h2>";
    echo "<pre>";
    
    // Check if table exists
    $checkTable = "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$dbName' AND TABLE_NAME = 'notifications'";
    $result = mysqli_query($conn, $checkTable);
    
    if (mysqli_num_rows($result) > 0) {
        echo "✅ Notifications table already exists!\n";
    } else {
        echo "Creating notifications table...\n";
        
        // Create notifications table
        $createTable = "CREATE TABLE notifications (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            booking_id BIGINT UNSIGNED NOT NULL,
            type ENUM('new_booking', 'payment_confirmed', 'booking_cancelled') DEFAULT 'new_booking',
            title VARCHAR(255) NOT NULL,
            message LONGTEXT,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_is_read (is_read),
            INDEX idx_created_at (created_at),
            FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if (mysqli_query($conn, $createTable)) {
            echo "✅ Notifications table created successfully!\n";
        } else {
            throw new Exception("Error creating table: " . mysqli_error($conn));
        }
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "✅ Migration completed successfully!\n";
    echo str_repeat("=", 60) . "\n";
    echo "\nNext steps:\n";
    echo "1. Go to: https://cizynails-booking.web.id/admin/dashboard\n";
    echo "2. Notification bell should now work!\n";
    echo "</pre>";
    
    mysqli_close($conn);
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Error</h2>";
    echo "<pre style='color: red;'>";
    echo "Error: " . $e->getMessage() . "\n";
    echo "</pre>";
}
?>
