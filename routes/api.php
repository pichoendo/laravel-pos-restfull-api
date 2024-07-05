<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeSalaryController;
use App\Http\Controllers\EmployeeSalesCommissionLogController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemStockController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberPoinLogController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalesController;
use App\Models\EmployeeSalary;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Authentication routes
    Route::controller(AuthController::class)->group(function () {
        Route::post('login', 'login')->name('login');
        Route::post('refresh', 'refresh')->name('refresh');
    });

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {

        // category routes
        Route::apiResource("category", CategoryController::class);
        Route::prefix('category')->group(function () {
            Route::get('{category}/items', [CategoryController::class, 'getCategoryListOfItems']);
        });
        // coupon routes
        Route::apiResource("coupon", CouponController::class);
        Route::prefix('coupon')->group(function () {
            Route::controller(CouponController::class)->group(function () {
                Route::get('mostUsed', 'getMostUsedCoupons');
                Route::get('{coupon}/usage', [CouponController::class, 'getCouponUsage']);
            });
        });

        // Employee routes
        Route::apiResource("employee", EmployeeController::class);
        Route::apiResource("employee.commission", EmployeeSalesCommissionLogController::class)->shallow()->only(['index','show']);


        Route::prefix('employee')->group(function () {
            Route::get('commission', [EmployeeSalesCommissionLogController::class, 'index']);
            Route::apiResource("salary", EmployeeSalaryController::class)->only(['index','show']);
            Route::prefix('{employee}')->group(function () {
                Route::controller(EmployeeController::class)->group(function () {
                    Route::get('sales', 'getEmployeeSalesList');
                    Route::get('commission', 'getEmployeeCommissionLog');
                });
            });
        });

        // Member routes
        Route::apiResource("member", MemberController::class);
        Route::prefix('member')->group(function () {
            Route::get('poin', [MemberPoinLogController::class, 'index']);
            Route::controller(MemberController::class)->group(function () {
                Route::get('royal', 'getRoyalCustomer');
                Route::prefix('{member}')->group(function () {
                    Route::get('sales', 'getMemberSalesList');
                    Route::get('point', 'getMemberPointLog');
                });
            });
        });

        // Item routes
        Route::apiResource("item", ItemController::class);
        Route::prefix('item')->group(function () {
            Route::controller(ItemController::class)->group(function () {
                Route::get('topSelling', 'getTopSellingItem');
                Route::get('outOfStock', 'getOutOfStockItems');
                Route::prefix('{item}')->group(function () {
                    Route::get('stock', 'getItemStocks');
                });
            });
            Route::apiResource("stock", ItemStockController::class)->shallow();
        });

        // Sales routes
        Route::apiResource("sales", SalesController::class);

        // Role routes
        Route::apiResource("role", RoleController::class);
    });
});
