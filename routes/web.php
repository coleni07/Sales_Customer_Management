<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\McmController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PurchaseHistoryController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\SupportFeedbackController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\WorkflowController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/', DashboardController::class . '@index')->name('dashboard');

// Sales-Order
Route::get('/sales-orders', [SalesOrderController::class, 'index'])->name('sales-orders.index');
Route::get('/sales-orders/{salesOrder}', [SalesOrderController::class, 'show'])->name('sales-orders.show');
Route::put('/sales-orders/{salesOrder}', [SalesOrderController::class, 'update'])->name('sales-orders.update');
Route::post('/sales-orders/{salesOrder}/simulate-webhook', [SalesOrderController::class, 'simulateWebhook'])->name('sales-orders.simulate-webhook');

// Support System 
Route::get('/support', [SupportTicketController::class, 'index'])->name('support.index');
Route::get('/support/feedback/{ticket}', [SupportFeedbackController::class, 'create'])->name('support.feedback.create');
Route::post('/support/feedback', [SupportFeedbackController::class, 'store'])->name('support.feedback.store');

// Customers module
Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
Route::get('/purchase-history', [PurchaseHistoryController::class, 'index'])->name('purchase-history.index');

// Reports
Route::get('/reports/sales', [SalesReportController::class, 'index'])->name('reports.sales');
Route::get('/reports/sales/export', [SalesReportController::class, 'export'])->name('reports.sales.export');
Route::get('/reports/sales/products', [SalesReportController::class, 'productDetail'])->name('reports.sales.products');
Route::get('/reports/sales/regional', [SalesReportController::class, 'regionalDetail'])->name('reports.sales.regional');
Route::get('/reports/sales/representatives', [SalesReportController::class, 'repDetail'])->name('reports.sales.reps');

// MCM (Marketing Campaign Management)
Route::get('/mcm', [McmController::class, 'index'])->name('mcm.index');
Route::get('/campaigns/create', [CampaignController::class, 'create'])->name('campaigns.create');
Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
Route::get('/workflows/create', [WorkflowController::class, 'create'])->name('workflow.create');
Route::post('/workflows', [WorkflowController::class, 'store'])->name('workflow.store');

// Exit
Route::get('/exit', [PageController::class, 'show'])->defaults('page', 'exit')->name('exit.index');