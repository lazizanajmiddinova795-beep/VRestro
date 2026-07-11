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
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ChefController;
use App\Http\Controllers\ShiftController;

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
    Route::put('/menu/foods/{id}', [MenuController::class, 'update']); // Using PUT to allow image upload on update with _method=PUT
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
    
    // Cashier routes
    Route::middleware('permission:view cashier dashboard')->group(function () {
        Route::get('/cashier/tables', [TableController::class, 'cashierTables']);
        Route::post('/orders/{id}/print-status', [OrderController::class, 'updatePrintStatus']);
        Route::post('/payments/{id}/print-status', [PaymentController::class, 'updatePrintStatus']);
        Route::get('/orders/{id}/print-data', [OrderController::class, 'getPrintData']);
    });

    // Chef / KDS routes
    Route::middleware('permission:view kitchen panel')->group(function () {
        Route::get('/chef/items', [ChefController::class, 'index']);
        Route::patch('/chef/items/{id}/status', [ChefController::class, 'updateStatus']);
        Route::get('/kitchen/foods', [ChefController::class, 'getMenu']);
        Route::post('/kitchen/foods/{id}/toggle', [ChefController::class, 'toggleFood']);
    });

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

    // Discounts
    Route::middleware('permission:manage discounts')->group(function () {
        Route::get('/discounts', [DiscountController::class, 'index']);
        Route::post('/discounts', [DiscountController::class, 'store']);
        Route::put('/discounts/{id}', [DiscountController::class, 'update']);
        Route::patch('/discounts/{id}/toggle', [DiscountController::class, 'toggleStatus']);
        Route::delete('/discounts/{id}', [DiscountController::class, 'destroy']);
    });
    Route::post('/discounts/validate-code', [DiscountController::class, 'validateCode']);

    // Reports
    Route::middleware('permission:view reports')->group(function () {
        Route::get('/reports/sales', [ReportController::class, 'getSalesReport']);
        Route::get('/reports/menu', [ReportController::class, 'getMenuReport']);
        Route::get('/reports/inventory', [ReportController::class, 'getInventoryReport']);
        Route::get('/reports/staff', [ReportController::class, 'getStaffReport']);
    });

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    // Settings
    Route::get('/settings', [SettingController::class, 'index']);
    Route::post('/settings/password', [SettingController::class, 'changePassword']);
    Route::post('/shift/close', [ShiftController::class, 'closeShift']);
    Route::middleware('permission:manage settings')->group(function () {
        Route::post('/settings', [SettingController::class, 'update']);
        Route::post('/settings/clear-cache', [SettingController::class, 'clearCache']);
        Route::post('/settings/test-telegram', [SettingController::class, 'testTelegram']);
    });

    // Waiter panel routes
    Route::middleware('permission:view waiter panel')->group(function () {
        Route::get('/waiter/tables', [\App\Http\Controllers\WaiterController::class, 'tables']);
        Route::post('/waiter/orders/submit', [\App\Http\Controllers\WaiterOrderController::class, 'submit']);
        Route::get('/waiter/orders/active-status', [\App\Http\Controllers\WaiterOrderController::class, 'activeStatus']);
        Route::delete('/waiter/order-item/{itemId}', [\App\Http\Controllers\WaiterOrderController::class, 'cancelItem']);
        Route::post('/cashier/orders/{orderId}/void', [\App\Http\Controllers\WaiterOrderController::class, 'voidOrder']);
        Route::get('/waiter/profile/daily-stats', [\App\Http\Controllers\WaiterController::class, 'dailyStats']);
    });
});
