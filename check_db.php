<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$users = DB::select('SELECT id, name, email, phone FROM users LIMIT 5');

echo "Users in database:\n";
foreach ($users as $user) {
    echo "ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Phone: " . ($user->phone ?? 'NULL') . "\n";
}

// Check if column exists
$columns = DB::select("SHOW COLUMNS FROM users");
echo "\n\nColumns in users table:\n";
foreach ($columns as $col) {
    echo "- {$col->Field}\n";
}
