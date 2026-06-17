<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

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
Route::prefix('admin')->name('admin.')->group(function () {

    // Login (sementara langsung ke dashboard, bisa ditambah auth nanti)
    // Route::get('/login', fn() => view('admin.login'))->name('login');
    // Route::post('/login', [AdminController::class, 'login'])->name('login.post');

    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');

    // Protected admin routes
    Route::middleware('auth')->group(function () {

        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Kategori
        Route::get('/categories', [AdminController::class, 'categoriesIndex'])->name('categories.index');
        Route::post('/categories', [AdminController::class, 'categoriesStore'])->name('categories.store');
        Route::put('/categories/{category}', [AdminController::class, 'categoriesUpdate'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminController::class, 'categoriesDestroy'])->name('categories.destroy');

        // Pengguna
        Route::get('/users', [AdminController::class, 'usersIndex'])->name('users.index');
        Route::patch('/users/{user}/verify', [AdminController::class, 'usersVerify'])->name('users.verify');
        Route::patch('/users/{user}/toggle', [AdminController::class, 'usersToggle'])->name('users.toggle');

        // Booking
        Route::get('/bookings', [AdminController::class, 'bookingsIndex'])->name('bookings.index');
        Route::get('/bookings/{booking}', [AdminController::class, 'bookingsShow'])->name('bookings.show');

        // Dispute
        Route::get('/disputes', [AdminController::class, 'disputesIndex'])->name('disputes.index');
        Route::get('/disputes/{dispute}', [AdminController::class, 'disputesShow'])->name('disputes.show');
        Route::patch('/disputes/{dispute}/resolve', [AdminController::class, 'disputesResolve'])->name('disputes.resolve');

        // Notifikasi Sistem
        Route::get('/notifications', [AdminController::class, 'notificationsIndex'])->name('notifications.index');
    });
});