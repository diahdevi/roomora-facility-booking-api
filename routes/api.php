<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\BookingApprovalController;
use App\Http\Controllers\Api\Admin\FacilityController as AdminFacilityController;
use App\Http\Controllers\Api\FacilityController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum'); 

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (user biasa)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Facilities (user)
    Route::get('/facilities', [FacilityController::class, 'index']);
    Route::get('/facilities/{facility}', [FacilityController::class, 'show']);
    Route::get('/facilities/{facility}/availability', [FacilityController::class, 'availability']);

    // Bookings (user)
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/my-bookings', [BookingController::class, 'myBookings']);
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
});

// Protected routes (admin only)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/bookings', [BookingApprovalController::class, 'index']);
    Route::patch('/bookings/{booking}/approve', [BookingApprovalController::class, 'approve']);
    Route::patch('/bookings/{booking}/reject', [BookingApprovalController::class, 'reject']);

    Route::get('/facilities', [AdminFacilityController::class, 'index']);
    Route::post('/facilities', [AdminFacilityController::class, 'store']);
    Route::put('/facilities/{facility}', [AdminFacilityController::class, 'update']);
    Route::delete('/facilities/{facility}', [AdminFacilityController::class, 'destroy']);
});