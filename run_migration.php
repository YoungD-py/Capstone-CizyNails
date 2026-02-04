<?php
/**
 * Migration Runner Script
 * Akses di browser untuk menjalankan migrations
 */

require __DIR__ . '/bootstrap/app.php';

try {
    $kernel = app(\Illuminate\Contracts\Console\Kernel::class);
    
    echo "<pre>";
    echo "Running migrations...\n";
    echo str_repeat("=", 60) . "\n";
    
    $kernel->call('migrate');
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "✅ Migrations completed successfully!\n";
    echo "\nNotifications table created. Access admin dashboard now.";
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<pre>";
    echo "❌ Error running migrations:\n";
    echo $e->getMessage() . "\n";
    echo "\nTrace:\n";
    echo $e->getTraceAsString();
    echo "</pre>";
}
?>
