<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SyncController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('/receive-sync', [SyncController::class, 'receiveSync'])->name('api.receive_sync');

// ---------------------------------------------------------
// FRONTEND HEADLESS API ROUTES
// ---------------------------------------------------------

// Public Routes
Route::post('/register', [\App\Http\Controllers\Api\AuthApiController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\Api\AuthApiController::class, 'login']);

Route::get('/settings', [\App\Http\Controllers\Api\WebsiteSettingsApiController::class, 'index']);
Route::get('/categories', [\App\Http\Controllers\Api\CategoryApiController::class, 'index']);
Route::get('/products', [\App\Http\Controllers\Api\ProductApiController::class, 'index']);
Route::get('/products/{id}', [\App\Http\Controllers\Api\ProductApiController::class, 'show']);
Route::post('/checkout', [\App\Http\Controllers\Api\CheckoutApiController::class, 'placeOrder']);
Route::post('/checkout/validate-coupon', [\App\Http\Controllers\Api\CheckoutApiController::class, 'validateCoupon']);

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\Api\AuthApiController::class, 'logout']);
    Route::get('/user', [\App\Http\Controllers\Api\AuthApiController::class, 'user']);
    Route::get('/customer/orders', [\App\Http\Controllers\Api\CustomerDashboardController::class, 'getOrders']);
});
// Distributor Reports
Route::get('/distributor-stocks/{distributorId}', [App\Http\Controllers\DistributorReportController::class, 'getDistributorStock']);
Route::post('/distributor-reports', [App\Http\Controllers\DistributorReportController::class, 'storeReport']);
