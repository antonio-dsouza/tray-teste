<?php

use App\Http\Controllers\Api\Sales\SaleController;
use App\Http\Controllers\Api\Sellers\SellerController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware(['jwt.auth'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);

    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    Route::middleware(['permission:view_sellers'])->group(function () {
        Route::get('/sellers', [SellerController::class, 'index']);
    });
    Route::middleware(['permission:create_sellers'])->group(function () {
        Route::post('/sellers', [SellerController::class, 'store']);
    });

    Route::middleware(['permission:view_sales'])->group(function () {
        Route::get('/sales', [SaleController::class, 'index']);
        Route::get('/sellers/{sellerId}/sales', [SaleController::class, 'bySeller']);
    });
    Route::middleware(['permission:create_sales'])->group(function () {
        Route::post('/sales', [SaleController::class, 'store']);
    });

    Route::middleware(['permission:resend_commissions'])->group(function () {
        Route::post('/admin/sellers/{sellerId}/resend-commission', [SellerController::class, 'resendSellerCommission']);
        Route::post('/admin/sales/{saleId}/resend-commission', [SaleController::class, 'resendSaleCommission']);
    });

    Route::middleware(['permission:run_daily_mails'])->group(function () {
        Route::post('/admin/run-daily-mails', [SellerController::class, 'runDailyMails']);
    });
});
