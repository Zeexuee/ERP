<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductionRequestController;
use App\Http\Controllers\SalesOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Customers CRUD
Route::resource('customers', CustomerController::class);

// Products (Read-Only stock view for Sales)
Route::resource('products', ProductController::class)->only(['index']);

// Sales Orders
Route::resource('sales-orders', SalesOrderController::class)->only(['index', 'create', 'store', 'show']);
Route::patch('sales-orders/{salesOrder}/status', [SalesOrderController::class, 'updateStatus'])->name('sales-orders.update-status');
Route::post('sales-orders/{salesOrder}/generate-invoice', [SalesOrderController::class, 'generateInvoice'])->name('sales-orders.generate-invoice');

// Production Requests (Read-Only for Sales)
Route::resource('production-requests', ProductionRequestController::class)->only(['index', 'show']);

// Invoices & Payments
Route::resource('invoices', InvoiceController::class)->only(['index', 'show']);
Route::post('invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('invoices.payments.store');
