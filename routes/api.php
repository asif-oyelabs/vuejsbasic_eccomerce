<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\EnquiryController;
use App\Http\Controllers\Api\EnquirylistController;


Route::middleware(['auth:sanctum', 'admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/dashboard', function () {
            return response()->json([
                'message' => 'Welcome to Admin Dashboard',
            ]);
        });
    });

    
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public enquiry endpoint (no auth required)
Route::get('/contact', [EnquiryController::class, 'index']);
Route::post('/contact', [EnquiryController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // User management routes
    Route::apiResource('users', UserController::class);
    
    // Enquiry management routes
    Route::apiResource('enquiries', EnquirylistController::class);
});