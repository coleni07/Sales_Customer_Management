<?php

use App\Http\Controllers\Api\FinanceExportController;
use App\Http\Controllers\Api\InventoryExportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Outbound Integration APIs — Sales Module
|--------------------------------------------------------------------------
|
| These are the two REST APIs this group (Sales) exposes so that OTHER
| ERP module groups can pull our data as their INPUT. URL naming follows
| the class-wide convention: /api/{target-module}/{data}-export
|
|   1) Finance & Accounting  -> /api/finance/sales-orders-export
|   2) Inventory / Warehouse -> /api/inventory/order-fulfillments-export
|
| NOTE: open (no API key) so they can be viewed directly in a browser for
| demo purposes, matching the rest of the class. See VerifyPartnerApiKey.php
| if you want to switch the key requirement back on later (just wrap this
| group in Route::middleware('partner.api')->group(...) again).
|
*/

// API #1: Sales -> Finance & Accounting
Route::get('/finance/sales-orders-export', [FinanceExportController::class, 'index'])
    ->name('api.finance.sales-orders.index');
Route::get('/finance/sales-orders-export/{salesOrder}', [FinanceExportController::class, 'show'])
    ->name('api.finance.sales-orders.show');

// API #2: Sales -> Inventory / Warehouse
Route::get('/inventory/order-fulfillments-export', [InventoryExportController::class, 'index'])
    ->name('api.inventory.order-fulfillments.index');
Route::get('/inventory/order-fulfillments-export/{salesOrder}', [InventoryExportController::class, 'show'])
    ->name('api.inventory.order-fulfillments.show');
