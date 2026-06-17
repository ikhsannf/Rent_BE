<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ListingController;
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\ReviewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/listings', [ListingController::class, 'index']);
Route::get('/listings/{listing}', [ListingController::class, 'show']);
Route::get('/listings/{listing}/reviews', [ReviewController::class, 'listingReviews']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);

    // Borrower Routes
    Route::middleware('role:borrower')->group(function () {
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::post('/reviews', [ReviewController::class, 'store']);
    });

    // Lender Routes
    Route::middleware('role:lender')->group(function () {
        Route::get('/lender/listings', [ListingController::class, 'myListings']);
        Route::post('/lender/listings', [ListingController::class, 'store']);
        Route::put('/lender/listings/{listing}', [ListingController::class, 'update']);
        Route::delete('/lender/listings/{listing}', [ListingController::class, 'destroy']);
    });

    // Shared Routes (Borrower & Lender)
    Route::get('/bookings', [BookingController::class, 'myBookings']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus']);
    Route::post('/bookings/{booking}/payment-proof', [BookingController::class, 'uploadPaymentProof']);
    
});
