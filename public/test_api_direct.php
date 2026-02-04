<?php
/**
 * Direct API Test - Bypass Laravel routing
 * Access via: https://cizynails-booking.web.id/test_api_direct.php
 */

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: application/json');

try {
    // Get all notifications directly
    $notifications = \App\Models\Notification::with('booking.user')
        ->orderBy('created_at', 'desc')
        ->get();
    
    $formatted = $notifications->map(function($n) {
        return [
            'id' => $n->id,
            'booking_id' => $n->booking_id,
            'customer_name' => ($n->booking && $n->booking->user) ? $n->booking->user->name : 'Unknown',
            'type' => $n->type,
            'title' => $n->title,
            'message' => $n->message,
            'is_read' => $n->is_read,
            'created_at' => $n->created_at->format('Y-m-d H:i:s'),
        ];
    });
    
    echo json_encode([
        'success' => true,
        'total' => $notifications->count(),
        'unread_count' => $notifications->where('is_read', 0)->count(),
        'notifications' => $formatted
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
