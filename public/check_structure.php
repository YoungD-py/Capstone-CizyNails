<?php
/**
 * Cek Struktur Direktori Production
 * Akses via: https://cizynails-booking.web.id/check_structure.php
 */

echo "<h2>🔍 Checking Production Directory Structure</h2>";
echo "<pre>";

echo "📂 Current Directory: " . __DIR__ . "\n";
echo "📂 Real Path: " . realpath(__DIR__) . "\n\n";

echo "============================================================\n";
echo "Checking Laravel Files:\n";
echo "============================================================\n\n";

// Check possible locations
$possiblePaths = [
    'vendor/autoload.php' => __DIR__ . '/../vendor/autoload.php',
    'bootstrap/app.php' => __DIR__ . '/../bootstrap/app.php',
    'app/Models/Notification.php' => __DIR__ . '/../app/Models/Notification.php',
    'routes/api.php' => __DIR__ . '/../routes/api.php',
    '.env file' => __DIR__ . '/../.env',
];

foreach ($possiblePaths as $name => $path) {
    if (file_exists($path)) {
        echo "✅ FOUND: $name\n";
        echo "   Path: " . realpath($path) . "\n\n";
    } else {
        echo "❌ NOT FOUND: $name\n";
        echo "   Checked: $path\n\n";
    }
}

echo "============================================================\n";
echo "Parent Directory Contents:\n";
echo "============================================================\n\n";

$parentDir = dirname(__DIR__);
if (is_dir($parentDir)) {
    $items = scandir($parentDir);
    foreach ($items as $item) {
        if ($item == '.' || $item == '..') continue;
        $fullPath = $parentDir . '/' . $item;
        $type = is_dir($fullPath) ? '📁 DIR ' : '📄 FILE';
        echo "$type  $item\n";
    }
}

echo "\n============================================================\n";
echo "Database Config Check:\n";
echo "============================================================\n\n";

$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    preg_match('/DB_DATABASE=(.+)/', $envContent, $dbName);
    preg_match('/DB_USERNAME=(.+)/', $envContent, $dbUser);
    if ($dbName) echo "✅ DB_DATABASE: " . trim($dbName[1]) . "\n";
    if ($dbUser) echo "✅ DB_USERNAME: " . trim($dbUser[1]) . "\n";
}

echo "</pre>";
