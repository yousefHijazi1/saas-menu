<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MenuItemController;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\WaiterCallController;
use Illuminate\Support\Facades\Route;

// Public routes (no auth required)
Route::withoutMiddleware('auth:sanctum')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/restaurants', [RestaurantController::class, 'index']);
    Route::get('/restaurants/{restaurant}', [RestaurantController::class, 'show']);
    Route::get('/restaurants/{restaurant}/menu', [RestaurantController::class, 'menu']);
    Route::get('/restaurants/{restaurant}/tables', [TableController::class, 'index']);
    Route::post('/restaurants/{restaurant}/waiter-call', [WaiterCallController::class, 'store']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::post('/restaurants', [RestaurantController::class, 'store']);
        Route::put('/restaurants/{restaurant}', [RestaurantController::class, 'update']);
        Route::delete('/restaurants/{restaurant}', [RestaurantController::class, 'destroy']);

        // Admin dashboard
        Route::get('/admin/restaurants', [RestaurantController::class, 'index']);
    });

    // Restaurant owner routes (can manage their own restaurant)
    Route::middleware('role:restaurant,admin')->group(function () {
        // Waiter calls
        Route::get('/restaurants/{restaurant}/waiter-calls', [WaiterCallController::class, 'index']);
        Route::put('/waiter-calls/{waiterCall}/handled', [WaiterCallController::class, 'markHandled']);
        // Categories
        Route::get('/restaurants/{restaurant}/categories', [CategoryController::class, 'index']);
        Route::post('/restaurants/{restaurant}/categories', [CategoryController::class, 'store']);
        Route::get('/categories/{category}', [CategoryController::class, 'show']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        // Menu Items
        Route::get('/restaurants/{restaurant}/menu-items', [MenuItemController::class, 'index']);
        Route::post('/restaurants/{restaurant}/menu-items', [MenuItemController::class, 'store']);
        Route::get('/menu-items/{menuItem}', [MenuItemController::class, 'show']);
        Route::put('/menu-items/{menuItem}', [MenuItemController::class, 'update']);
        Route::delete('/menu-items/{menuItem}', [MenuItemController::class, 'destroy']);

        // Tables
        Route::get('/restaurants/{restaurant}/tables', [TableController::class, 'index']);
        Route::post('/restaurants/{restaurant}/tables', [TableController::class, 'store']);
        Route::get('/tables/{table}', [TableController::class, 'show']);
        Route::put('/tables/{table}', [TableController::class, 'update']);
        Route::delete('/tables/{table}', [TableController::class, 'destroy']);

        // Orders (store is public for customer menu)
        Route::get('/restaurants/{restaurant}/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus']);
        Route::delete('/orders/{order}', [OrderController::class, 'destroy']);
    });
});
