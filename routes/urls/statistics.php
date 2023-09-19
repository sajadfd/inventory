<?php

use App\Enums\PermissionEnum;
use App\Http\Controllers\StatisticsController;

Route::group(['prefix' => 'statistics', 'middleware' => 'permission:' . PermissionEnum::VIEW_STATISTICS], function () {

    Route::get('purchases', [StatisticsController::class, 'purchaseStats']);
    Route::get('sales', [StatisticsController::class, 'salesStats']);
    Route::get('suppliers', [StatisticsController::class, 'suppliersStats']);
    Route::get('customers', [StatisticsController::class, 'customersStats']);
    Route::get('products', [StatisticsController::class, 'productsStats']);
    Route::get('product_transactions', [StatisticsController::class, 'productTransactionsStats']);
    Route::get('services', [StatisticsController::class, 'servicesStats']);
    Route::get('cars', [StatisticsController::class, 'carsStats']);
    Route::get('earnings', [StatisticsController::class, 'earningsStats']);
    Route::get('inventory_earnings', [StatisticsController::class, 'inventoryEarningsStats']);

});
