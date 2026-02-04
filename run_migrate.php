<?php
// Run migrations on production
require '/home/cizynail/Cizy-Nails-Project/bootstrap/app.php';

$app = require '/home/cizynail/Cizy-Nails-Project/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');

echo "Running migrations...\n";
$kernel->call('migrate');
echo "Migrations completed!\n";
?>
