<?php
/**
 * Clear Route Cache in Production
 * Access via: https://cizynails-booking.web.id/clear_routes.php
 */

echo "<h2>🔧 Clearing Production Route Cache</h2>";
echo "<pre>";

// Clear route cache
$routeCachePath = __DIR__ . '/../bootstrap/cache/routes-v7.php';
if (file_exists($routeCachePath)) {
    unlink($routeCachePath);
    echo "✅ Route cache cleared: routes-v7.php deleted\n";
} else {
    echo "ℹ️  No route cache file found (already cleared)\n";
}

// Clear config cache
$configCachePath = __DIR__ . '/../bootstrap/cache/config.php';
if (file_exists($configCachePath)) {
    unlink($configCachePath);
    echo "✅ Config cache cleared: config.php deleted\n";
} else {
    echo "ℹ️  No config cache file found\n";
}

// Clear view cache
$viewCachePath = __DIR__ . '/../storage/framework/views';
if (is_dir($viewCachePath)) {
    $files = glob($viewCachePath . '/*');
    $count = 0;
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
            $count++;
        }
    }
    echo "✅ View cache cleared: {$count} files deleted\n";
}

echo "\n============================================================\n";
echo "✅ ALL CACHES CLEARED!\n";
echo "============================================================\n\n";

echo "Next: Go to admin dashboard and check notification bell\n";
echo "https://cizynails-booking.web.id/admin/dashboard\n";

echo "</pre>";
