<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\AdminDashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
// Save booking intent for guest users
Route::post('/save-booking-intent', function (Request $request) {
    session([
        'booking_intent' => [
            'service_id' => $request->input('service_id'),
            'date' => $request->input('date'),
            'time' => $request->input('time'),
            'needs_removal' => $request->input('needs_removal', 0),
        ]
    ]);
    
    return response()->json(['success' => true]);
});


Route::get('/bookings/available-times', [BookingController::class, 'getAvailableTimes']);

Route::match(['get', 'post'], '/midtrans/webhook', function (Request $request) {
    if ($request->isMethod('get')) {
        return response()->json([
            'status' => 'ok',
            'message' => 'Midtrans webhook endpoint is ready.',
        ], 200);
    }

    $notification = $request->all();
    $orderId = $notification['order_id'] ?? null;

    if (!$orderId || !is_string($orderId)) {
        \Log::warning('Midtrans webhook received without order_id', [
            'payload' => $notification,
        ]);
        return response()->json([
            'status' => 'ignored',
            'message' => 'Missing order_id in notification.',
        ], 200);
    }

    if (strpos($orderId, 'BOOKING-') !== 0) {
        \Log::warning('Midtrans webhook order_id not recognized', [
            'order_id' => $orderId,
            'payload' => $notification,
        ]);
        return response()->json([
            'status' => 'ignored',
            'message' => 'Unrecognized order_id format.',
        ], 200);
    }

    $midtransService = new \App\Services\MidtransService();
    $result = $midtransService->handleNotification($notification);

    if ($result) {
        return response()->json(['status' => 'success'], 200);
    }

    return response()->json([
        'status' => 'error',
        'message' => 'Notification processing failed.',
    ], 500);
});

Route::middleware(['api.session.auth', 'auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // User profile routes
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    
    // Booking routes
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
    Route::post('/bookings/{booking}/retry-payment', [BookingController::class, 'retryPayment']);
    Route::post('/bookings/{booking}/reschedule', [BookingController::class, 'reschedule']);
    
    Route::middleware('admin')->group(function () {
        Route::post('/services', [ServiceController::class, 'store']);
        Route::put('/services/{service}', [ServiceController::class, 'update']);
        Route::delete('/services/{service}', [ServiceController::class, 'destroy']);
        
        Route::post('/schedules', [ScheduleController::class, 'store']);
        Route::put('/schedules/{schedule}', [ScheduleController::class, 'update']);
        Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy']);
        
        // Notification routes
        Route::get('/admin/notifications', [AdminDashboardController::class, 'getNotifications']);
        Route::post('/admin/notifications/{id}/read', [AdminDashboardController::class, 'markNotificationAsRead']);
        Route::post('/admin/notifications/read-all', [AdminDashboardController::class, 'markAllNotificationsAsRead']);
        Route::post('/admin/notifications/test', [AdminDashboardController::class, 'createTestNotification']); // For testing
    });
});
