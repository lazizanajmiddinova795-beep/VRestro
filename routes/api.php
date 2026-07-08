<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

use App\Http\Controllers\DashboardController;

use App\Http\Controllers\OrderController;

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PaymentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-face', [AuthController::class, 'verifyFace']);
});

Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

Route::middleware('auth:sanctum')->group(function () {
    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);

    // Categories
    Route::get('/menu/categories', [CategoryController::class, 'index']);
    Route::post('/menu/categories', [CategoryController::class, 'store']);
    Route::put('/menu/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/menu/categories/{id}', [CategoryController::class, 'destroy']);

    // Foods (Dishes)
    Route::get('/menu/foods', [MenuController::class, 'index']);
    Route::post('/menu/foods', [MenuController::class, 'store']);
    Route::get('/menu/foods/{id}', [MenuController::class, 'show']);
    Route::post('/menu/foods/{id}', [MenuController::class, 'update']); // Using POST to allow image upload on update
    Route::patch('/menu/foods/{id}/toggle', [MenuController::class, 'toggleAvailability']);
    Route::delete('/menu/foods/{id}', [MenuController::class, 'destroy']);

    // Recipes
    Route::get('/menu/foods/{id}/recipe', [RecipeController::class, 'show']);
    Route::post('/menu/foods/{id}/recipe', [RecipeController::class, 'store']);

    // Ingredients
    Route::get('/ingredients', [IngredientController::class, 'index']);
    Route::post('/ingredients', [IngredientController::class, 'store']);
    Route::get('/ingredients/{id}', [IngredientController::class, 'show']);
    Route::put('/ingredients/{id}', [IngredientController::class, 'update']);
    Route::post('/ingredients/{id}/adjust', [IngredientController::class, 'adjust']);
    Route::delete('/ingredients/{id}', [IngredientController::class, 'destroy']);

    // Warehouse
    Route::get('/warehouse/transactions', [WarehouseController::class, 'index']);
    Route::get('/warehouse/ingredients/{id}/timeline', [WarehouseController::class, 'timeline']);
    Route::post('/warehouse/kirim', [WarehouseController::class, 'kirim']);
    Route::post('/warehouse/chiqim', [WarehouseController::class, 'chiqim']);
    Route::post('/warehouse/inventarizatsiya', [WarehouseController::class, 'inventarizatsiya']);

    // Tables
    Route::get('/tables', [TableController::class, 'index']);
    Route::post('/tables', [TableController::class, 'store']);
    Route::put('/tables/{id}', [TableController::class, 'update']);
    Route::patch('/tables/{id}/status', [TableController::class, 'updateStatus']);
    Route::delete('/tables/{id}', [TableController::class, 'destroy']);

    // Staff
    Route::get('/staff', [StaffController::class, 'index']);
    Route::post('/staff', [StaffController::class, 'store']);
    Route::put('/staff/{id}', [StaffController::class, 'update']);
    Route::patch('/staff/{id}/toggle', [StaffController::class, 'toggleStatus']);
    Route::delete('/staff/{id}', [StaffController::class, 'destroy']);

    // Customers
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::get('/customers/analytics', [CustomerController::class, 'analytics']);
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::put('/customers/{id}', [CustomerController::class, 'update']);
    Route::post('/customers/{id}/adjust', [CustomerController::class, 'adjust']);
    Route::delete('/customers/{id}', [CustomerController::class, 'destroy']);

    // Payments
    Route::middleware('permission:manage payments')->group(function () {
        Route::get('/payments', [PaymentController::class, 'index']);
        Route::get('/payments/revenue', [PaymentController::class, 'revenueBreakdown']);
        Route::post('/payments', [PaymentController::class, 'store']);
        Route::post('/payments/{id}/refund', [PaymentController::class, 'refund']);
    });
});
