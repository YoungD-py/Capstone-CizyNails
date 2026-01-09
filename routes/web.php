<?php

use App\Http\Controllers\LandingController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NailArtistDashboardController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register')->middleware('guest');
Route::post('/register', [AuthController::class, 'register'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/booking', [CustomerDashboardController::class, 'bookingForm'])->name('booking.form');
    Route::post('/bookings/{id}/cancel', [CustomerDashboardController::class, 'cancelBooking'])->name('customer.cancel-booking');
    Route::get('/profile/edit', [UserController::class, 'showEditProfile'])->name('profile.edit');
    Route::put('/profile/update', [UserController::class, 'updateProfileWeb'])->name('profile.update');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('/types', TypeController::class, ['as' => 'admin']);
    Route::get('/bookings', [AdminDashboardController::class, 'bookings'])->name('admin.bookings');
    Route::post('/bookings/create', [AdminDashboardController::class, 'createBooking'])->name('admin.bookings.create');
    Route::get('/api/customers-list', [AdminDashboardController::class, 'getCustomersList'])->name('admin.api.customers');
    Route::get('/api/services-list', [AdminDashboardController::class, 'getServicesList'])->name('admin.api.services');
    Route::post('/bookings/bulk-delete', [AdminDashboardController::class, 'bulkDestroy'])->name('admin.bookings.bulk-destroy');
    Route::post('/bookings/{booking}/cancel', [AdminDashboardController::class, 'cancelBooking'])->name('admin.cancel-booking');
    Route::delete('/bookings/{booking}', [AdminDashboardController::class, 'destroy'])->name('admin.bookings.destroy');
    Route::get('/services', [AdminDashboardController::class, 'services'])->name('admin.services');
    Route::post('/services', [AdminDashboardController::class, 'storeService'])->name('admin.services.store');
    Route::put('/services/{service}', [AdminDashboardController::class, 'updateService'])->name('admin.services.update');
    Route::delete('/services/{service}', [AdminDashboardController::class, 'deleteService'])->name('admin.services.destroy');
    Route::get('/schedules', [AdminDashboardController::class, 'schedules'])->name('admin.schedules');
    Route::get('/customers', [AdminDashboardController::class, 'customers'])->name('admin.customers');
});

Route::middleware(['auth', 'nail_artist'])->prefix('nail-artist')->group(function () {
    Route::get('/dashboard', [NailArtistDashboardController::class, 'index'])->name('nail-artist.dashboard');
    Route::get('/bookings', [NailArtistDashboardController::class, 'bookings'])->name('nail-artist.bookings');
    Route::post('/bookings/{booking}/status', [NailArtistDashboardController::class, 'updateStatus'])->name('nail-artist.update-status');
});
