<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\McmController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PurchaseHistoryController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\SupportFeedbackController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\WorkflowController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// System Navigation & Notifications
Route::get('/exit', [PageController::class, 'show'])->defaults('page', 'exit')->name('exit.index');
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

// Sales Orders
Route::controller(SalesOrderController::class)->prefix('sales-orders')->as('sales-orders.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{salesOrder}', 'show')->name('show');
    Route::put('/{salesOrder}', 'update')->name('update');
    Route::post('/{salesOrder}/simulate-webhook', 'simulateWebhook')->name('simulate-webhook');
});

// Support System 
Route::controller(SupportTicketController::class)->prefix('support')->as('support.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/feedback/{ticket}', [SupportFeedbackController::class, 'create'])->name('feedback.create');
    Route::post('/feedback', [SupportFeedbackController::class, 'store'])->name('feedback.store');
});

// Customers Module
Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
Route::get('/purchase-history', [PurchaseHistoryController::class, 'index'])->name('purchase-history.index');

// Reports Module
Route::controller(SalesReportController::class)->prefix('reports/sales')->group(function () {
    Route::get('/', 'index')->name('reports.sales');
    Route::get('/export', 'export')->name('reports.sales.export');
    Route::get('/products', 'productDetail')->name('reports.sales.products');
    Route::get('/regional', 'regionalDetail')->name('reports.sales.regional');
    Route::get('/representatives', 'repDetail')->name('reports.sales.reps');
});

// MCM (Marketing Campaign Management)
Route::get('/mcm', [McmController::class, 'index'])->name('mcm.index');

Route::controller(CampaignController::class)->prefix('campaigns')->as('campaigns.')->group(function () {
    Route::get('/create', 'create')->name('create');
    Route::post('/', 'store')->name('store');
    Route::get('/{campaign}/edit', 'edit')->name('edit');
    Route::put('/{campaign}', 'update')->name('update');
});

Route::controller(WorkflowController::class)->prefix('workflows')->as('workflow.')->group(function () {
    Route::get('/create', 'create')->name('create');
    Route::post('/', 'store')->name('store');
});