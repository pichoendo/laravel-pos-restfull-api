<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeSalesCommissionLogController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemStockController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalesController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Authentication routes
    
    Route::controller(AuthController::class)->group(function () {
        Route::post('login', 'login')->name('login');
        Route::post('refresh', 'refresh')->name('refresh');
    });

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {

        // Employee routes
        Route::apiResource("employee", EmployeeController::class);
        Route::apiResource("employee.commission", EmployeeSalesCommissionLogController::class)->shallow();
        Route::get('employee/commission', [EmployeeSalesCommissionLogController::class, 'indexAll']);
        Route::prefix('employee')->group(function () {
            Route::get('{employee}/sales', [EmployeeController::class, 'getSalesDetails']);
        });

        // Member routes
        Route::apiResource("member", MemberController::class);

        // Item routes
        Route::apiResource("item", ItemController::class);
        Route::apiResource("item.stock", ItemStockController::class)->shallow();
        Route::get('items/stock', [ItemStockController::class, 'indexAll']);

        // Sales routes
        Route::apiResource("sales", SalesController::class);

        // Category routes
        Route::apiResource("category", CategoryController::class);

        // Role routes
        Route::apiResource("role", RoleController::class);
    });
});
