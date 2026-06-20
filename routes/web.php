<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FirebaseAdminController;
use App\Http\Controllers\Auth\Login;
use App\Http\Controllers\Auth\Logout;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => redirect()->route('admin.dashboard'));

// Login routes
Route::view('/login', 'auth.login')
    ->middleware('guest')
    ->name('login');

Route::post('/login', Login::class)
    ->middleware('guest');

// Logout route
Route::post('/logout', Logout::class)
    ->middleware('auth')
    ->name('logout');

// Admin Routes — semua harus login dulu
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [FirebaseAdminController::class, 'dashboard'])->name('dashboard');

    // Pengguna
    Route::get('/users', [FirebaseAdminController::class, 'users'])->name('users');
    Route::get('/users/{userId}', [FirebaseAdminController::class, 'showUser'])->name('users.show');

    // Barang
    Route::get('/listings', [FirebaseAdminController::class, 'listings'])->name('listings');
    Route::delete('/listings/{listingId}', [FirebaseAdminController::class, 'deleteListing'])->name('listings.delete');

    // Transaksi
    Route::get('/bookings', [FirebaseAdminController::class, 'bookings'])->name('bookings');
    Route::patch('/bookings/{bookingId}', [FirebaseAdminController::class, 'updateBookingStatus'])->name('bookings.update');
});
