<?php
/**
 * Check if API routes are loaded
 * Access via: https://cizynails-booking.web.id/check_routes.php
 */

echo "<h2>🔍 Checking API Routes Configuration</h2>";
echo "<pre>";

// Check if routes/api.php exists
$apiRoutesFile = __DIR__ . '/../routes/api.php';
if (file_exists($apiRoutesFile)) {
    echo "✅ routes/api.php exists\n";
    echo "   Location: " . realpath($apiRoutesFile) . "\n";
    echo "   Size: " . filesize($apiRoutesFile) . " bytes\n";
    echo "   Modified: " . date('Y-m-d H:i:s', filemtime($apiRoutesFile)) . "\n\n";
    
    // Check if notification routes are in the file
    $content = file_get_contents($apiRoutesFile);
    if (strpos($content, 'getNotifications') !== false) {
        echo "✅ getNotifications route found in api.php\n";
    } else {
        echo "❌ getNotifications route NOT found in api.php\n";
    }
    
    if (strpos($content, '/admin/notifications') !== false) {
        echo "✅ /admin/notifications path found in api.php\n";
    } else {
        echo "❌ /admin/notifications path NOT found in api.php\n";
    }
} else {
    echo "❌ routes/api.php NOT found!\n";
}

echo "\n============================================================\n";

// Check if controller exists
$controllerFile = __DIR__ . '/../app/Http/Controllers/AdminDashboardController.php';
if (file_exists($controllerFile)) {
    echo "✅ AdminDashboardController.php exists\n";
    echo "   Location: " . realpath($controllerFile) . "\n";
    echo "   Size: " . filesize($controllerFile) . " bytes\n";
    echo "   Modified: " . date('Y-m-d H:i:s', filemtime($controllerFile)) . "\n\n";
    
    // Check if getNotifications method exists
    $content = file_get_contents($controllerFile);
    if (strpos($content, 'function getNotifications') !== false) {
        echo "✅ getNotifications method found in controller\n";
    } else {
        echo "❌ getNotifications method NOT found in controller\n";
    }
} else {
    echo "❌ AdminDashboardController.php NOT found!\n";
}

echo "\n============================================================\n";

// Check RouteServiceProvider
$routeProvider = __DIR__ . '/../app/Providers/RouteServiceProvider.php';
if (file_exists($routeProvider)) {
    echo "✅ RouteServiceProvider.php exists\n";
    $content = file_get_contents($routeProvider);
    if (strpos($content, "Route::prefix('api')") !== false || strpos($content, 'routes/api.php') !== false) {
        echo "✅ API routes configuration found\n";
    }
}

echo "\n============================================================\n";
echo "\n📝 DIAGNOSIS:\n";
echo "If routes/api.php exists but route returns 404:\n";
echo "  → Need to re-upload routes/api.php to production\n";
echo "  → Use FileZilla or cPanel File Manager\n";
echo "  → Upload from: routes/api.php\n";
echo "  → Upload to: ~/Cizy-Nails-Project/routes/api.php\n";

echo "</pre>";
