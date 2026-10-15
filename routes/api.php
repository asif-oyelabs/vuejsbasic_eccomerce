<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\EnquiryController;
use App\Http\Controllers\Api\EnquirylistController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ProductController as PublicProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;



Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {

    Route::get('/dashboard', function () {return response()->json([ 'message' => 'Welcome to Admin Dashboard']);});
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('products', ProductController::class);
     Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::get('/orders/{id}', [AdminOrderController::class, 'show']);

});

    

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public enquiry endpoint (no auth required)
Route::get('/contact', [EnquiryController::class, 'index']);
Route::post('/contact', [EnquiryController::class, 'store']);
Route::get('/products', [PublicProductController::class, 'index']);
Route::get('/products/{slug}', [PublicProductController::class, 'show']);
Route::get('/product-categories', [PublicProductController::class, 'categories']);

// Public homepage APIs
Route::prefix('home')->group(function () {
    Route::get('/categories', [HomeController::class, 'categories']);
    Route::get('/products', [HomeController::class, 'products']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::patch('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);

    // Orders
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    
    // User management
    Route::apiResource('users', UserController::class);
    
    // Enquiry management
    Route::apiResource('enquiries', EnquirylistController::class);
});