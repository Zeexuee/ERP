<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CrmController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EcommerceController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Production\MaterialController;
use App\Http\Controllers\Production\ProductionBatchController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ProductionRequestController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\StinController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Authentication Routes (Guest)
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated Global Routes
Route::middleware('auth')->group(function () {
    Route::post('quick-switch', [AuthController::class, 'quickSwitch'])->name('quick-switch');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard Dispatcher: Arahkan setiap role ke dashboard divisi masing-masing
    Route::get('/', function () {
        /** @var User $user */
        $user = auth()->user();

        if ($user && $user->canAccessModule('sales')) {
            return app(DashboardController::class)->index();
        }

        return redirect()->route($user?->role?->defaultRouteName() ?? 'login');
    })->name('dashboard');

    // Modul Sales & Transaksi Penjualan (Khusus Sales & Super Role)
    Route::middleware('role:sales')->group(function () {

        // Customers Import / Export & CRUD
        Route::get('customers/export/excel', [CustomerController::class, 'exportExcel'])->name('customers.export.excel');
        Route::get('customers/template/excel', [CustomerController::class, 'templateExcel'])->name('customers.template.excel');
        Route::post('customers/import/excel', [CustomerController::class, 'importExcel'])->name('customers.import.excel');
        Route::resource('customers', CustomerController::class);

        // Sales Orders Import / Export & CRUD
        Route::get('sales-orders/export/excel', [SalesOrderController::class, 'exportExcel'])->name('sales-orders.export.excel');
        Route::get('sales-orders/template/excel', [SalesOrderController::class, 'templateExcel'])->name('sales-orders.template.excel');
        Route::post('sales-orders/import/excel', [SalesOrderController::class, 'importExcel'])->name('sales-orders.import.excel');
        Route::resource('sales-orders', SalesOrderController::class)->only(['index', 'create', 'store']);
        Route::patch('sales-orders/{salesOrder}/status', [SalesOrderController::class, 'updateStatus'])->name('sales-orders.update-status');
        Route::post('sales-orders/{salesOrder}/generate-invoice', [SalesOrderController::class, 'generateInvoice'])->name('sales-orders.generate-invoice');

        // Invoices Import / Export & CRUD
        Route::get('invoices/export/excel', [InvoiceController::class, 'exportExcel'])->name('invoices.export.excel');
        Route::get('invoices/template/excel', [InvoiceController::class, 'templateExcel'])->name('invoices.template.excel');
        Route::post('invoices/import/excel', [InvoiceController::class, 'importExcel'])->name('invoices.import.excel');
        Route::resource('invoices', InvoiceController::class)->only(['index', 'show']);
        Route::post('invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('invoices.payments.store');
    });

    // Katalog Produk & Stok (Sales hanya lihat; Produksi & Super Admin dapat mengedit)
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::middleware('role:production')->group(function () {
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
        Route::match(['put', 'patch'], 'products/{product}', [ProductController::class, 'update'])->name('products.update');
    });

    // Permintaan Produksi (Antrean Sales) & Detail Sales Order Acuan
    Route::resource('production-requests', ProductionRequestController::class)->only(['index', 'show']);
    Route::get('sales-orders/{salesOrder}', [SalesOrderController::class, 'show'])->name('sales-orders.show');

    // Detail Batch Produksi (Dapat diakses oleh Divisi Produksi dan Sales)
    Route::middleware('role:production,sales')->get('production/batches/{batch}', [ProductionBatchController::class, 'show'])->name('production.batches.show');

    // Modul Produksi (Pabrik & Manufaktur)
    Route::middleware('role:production')->prefix('production')->name('production.')->group(function () {
        Route::get('/', [ProductionController::class, 'dashboard'])->name('dashboard');
        Route::patch('requests/{productionRequest}/status', [ProductionController::class, 'updateStatus'])->name('update-status');

        // Barang Gudang (Bahan Baku & Inventaris)
        Route::get('materials', [MaterialController::class, 'index'])->name('materials.index');
        Route::get('materials/export-logs', [MaterialController::class, 'exportLogs'])->name('materials.export-logs');
        Route::post('materials', [MaterialController::class, 'storeMaterial'])->name('materials.store');
        Route::put('materials/{material}', [MaterialController::class, 'updateMaterial'])->name('materials.update');
        Route::post('materials/{material}/recount', [MaterialController::class, 'recountMaterial'])->name('materials.recount');
        Route::post('materials/{material}/sort', [MaterialController::class, 'sortMaterial'])->name('materials.sort');
        Route::get('materials/receipt/create', [MaterialController::class, 'createReceipt'])->name('materials.create-receipt');
        Route::post('materials/receipt', [MaterialController::class, 'storeReceipt'])->name('materials.store-receipt');

        // Proses Produksi (Manufaktur & Laporan Harian)
        Route::get('batches', [ProductionBatchController::class, 'index'])->name('batches.index');
        Route::get('batches/create', [ProductionBatchController::class, 'create'])->name('batches.create');
        Route::post('batches', [ProductionBatchController::class, 'store'])->name('batches.store');
        Route::post('batches/{batch}/materials', [ProductionBatchController::class, 'addMaterial'])->name('batches.add-material');
        Route::post('batches/{batch}/daily-logs', [ProductionBatchController::class, 'storeDailyLog'])->name('batches.store-log');
        Route::post('batches/{batch}/complete', [ProductionBatchController::class, 'complete'])->name('batches.complete');
    });

    // Modul STIN (Divisi Khusus)
    Route::middleware('role:stin')->prefix('stin')->name('stin.')->group(function () {
        Route::get('/', [StinController::class, 'dashboard'])->name('dashboard');
    });

    // Modul CRM (Manajemen CRM)
    Route::middleware('role:admin_crm')->prefix('crm')->name('crm.')->group(function () {
        Route::get('/', [CrmController::class, 'dashboard'])->name('dashboard');
    });

    // Modul E-Commerce (Manajemen E-Commerce)
    Route::middleware('role:admin_ecommerce')->prefix('ecommerce')->name('ecommerce.')->group(function () {
        Route::get('/', [EcommerceController::class, 'dashboard'])->name('dashboard');
    });
});
