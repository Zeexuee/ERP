<?php

use App\Enums\UserRole;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CrmController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EcommerceController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Production\MaterialCarveController;
use App\Http\Controllers\Production\MaterialController;
use App\Http\Controllers\Production\MaterialMeltController;
use App\Http\Controllers\Production\MaterialSortController;
use App\Http\Controllers\Production\ProductionColoringController;
use App\Http\Controllers\Production\ProductionFinishingController;
use App\Http\Controllers\Production\ProductionTembakController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ProductionRequestController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\StinController;
use App\Http\Controllers\StinItemController;
use App\Http\Controllers\StinLoanController;
use App\Http\Controllers\StinMutationController;
use App\Http\Controllers\StinScannerController;
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

        if ($user && $user->role === UserRole::STIN) {
            return redirect()->route('stin.dashboard');
        }

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

    // Modul Produksi (Pabrik & Manufaktur)
    Route::middleware('role:production')->prefix('production')->name('production.')->group(function () {
        Route::get('/', [ProductionController::class, 'dashboard'])->name('dashboard');
        Route::get('kanban', [ProductionController::class, 'kanban'])->name('kanban');
        Route::patch('requests/{productionRequest}/status', [ProductionController::class, 'updateStatus'])->name('update-status');

        // Barang Gudang (Bahan Baku & Inventaris)
        Route::get('materials', [MaterialController::class, 'index'])->name('materials.index');
        Route::get('materials/export-logs', [MaterialController::class, 'exportLogs'])->name('materials.export-logs');
        Route::post('materials', [MaterialController::class, 'storeMaterial'])->name('materials.store');
        Route::put('materials/{material}', [MaterialController::class, 'updateMaterial'])->name('materials.update');
        Route::post('materials/{material}/recount', [MaterialController::class, 'recountMaterial'])->name('materials.recount');
        Route::post('materials/{material}/sort', [MaterialController::class, 'sortMaterial'])->name('materials.sort');
        Route::post('materials/{material}/split', [MaterialController::class, 'sortMaterial'])->name('materials.split');
        Route::get('dryings', [MaterialController::class, 'dryingIndex'])->name('dryings.index');
        Route::get('dryings/create', [MaterialController::class, 'createDryingLog'])->name('dryings.create');
        Route::post('dryings', [MaterialController::class, 'storeDryingLog'])->name('dryings.store');
        Route::get('dryings/{log}/report', [MaterialController::class, 'showReportDryingLog'])->name('dryings.report.create');
        Route::post('dryings/{log}/report', [MaterialController::class, 'reportDryingLog'])->name('dryings.report');
        Route::get('materials/receipt/create', [MaterialController::class, 'createReceipt'])->name('materials.create-receipt');
        Route::post('materials/receipt', [MaterialController::class, 'storeReceipt'])->name('materials.store-receipt');

        // Proses Sortir Kayu Mandiri (Bahan Mentah -> Jemur -> Sortir -> Bahan Tembak Gudang)
        Route::get('sorts', [MaterialSortController::class, 'index'])->name('sorts.index');
        Route::get('sorts/create', [MaterialSortController::class, 'create'])->name('sorts.create');
        Route::post('sorts', [MaterialSortController::class, 'store'])->name('sorts.store');
        Route::get('sorts/{sortBatch}', [MaterialSortController::class, 'show'])->name('sorts.show')->whereNumber('sortBatch');
        Route::post('sorts/{sortBatch}/report', [MaterialSortController::class, 'storeReport'])->name('sorts.store-report')->whereNumber('sortBatch');

        // Proses Potong Ukir (Bahan Mentah -> Potong Ukir -> Bahan Susut dll)
        Route::get('carves', [MaterialCarveController::class, 'index'])->name('carves.index');
        Route::get('carves/create', [MaterialCarveController::class, 'create'])->name('carves.create');
        Route::post('carves', [MaterialCarveController::class, 'store'])->name('carves.store');
        Route::get('carves/{carveBatch}', [MaterialCarveController::class, 'show'])->name('carves.show')->whereNumber('carveBatch');
        Route::post('carves/{carveBatch}/report', [MaterialCarveController::class, 'storeReport'])->name('carves.store-report')->whereNumber('carveBatch');

        // Proses Pencairan Getah (Bahan Padat -> Cairan -> Pemantauan Penguapan)
        Route::get('melts', [MaterialMeltController::class, 'index'])->name('melts.index');
        Route::get('melts/create', [MaterialMeltController::class, 'create'])->name('melts.create');
        Route::post('melts', [MaterialMeltController::class, 'store'])->name('melts.store');
        Route::get('melts/{meltBatch}', [MaterialMeltController::class, 'show'])->name('melts.show')->whereNumber('meltBatch');
        Route::post('melts/{meltBatch}/report', [MaterialMeltController::class, 'storeReport'])->name('melts.store-report')->whereNumber('meltBatch');
        Route::post('melts/{meltBatch}/log', [MaterialMeltController::class, 'storeLog'])->name('melts.store-log')->whereNumber('meltBatch');
        Route::post('melts/{meltBatch}/complete', [MaterialMeltController::class, 'complete'])->name('melts.complete')->whereNumber('meltBatch');

        // Proses Tembak Kayu (Bahan Kayu + Resin -> Injeksi/Tembak -> Hasil Basah -> Sisa Resin Kembali -> Hasil Kering)
        Route::get('tembaks', [ProductionTembakController::class, 'index'])->name('tembaks.index');
        Route::get('tembaks/create', [ProductionTembakController::class, 'create'])->name('tembaks.create');
        Route::post('tembaks', [ProductionTembakController::class, 'store'])->name('tembaks.store');
        Route::get('tembaks/{tembakBatch}', [ProductionTembakController::class, 'show'])->name('tembaks.show')->whereNumber('tembakBatch');
        Route::post('tembaks/{tembakBatch}/report', [ProductionTembakController::class, 'storeReport'])->name('tembaks.store-report')->whereNumber('tembakBatch');
        Route::post('tembaks/{tembakBatch}/drying-logs', [ProductionTembakController::class, 'storeDryingLog'])->name('tembaks.store-drying-log')->whereNumber('tembakBatch');

        // Proses Celup dan Warna (Setelah Jemur Tembak)
        Route::get('colorings', [ProductionColoringController::class, 'index'])->name('colorings.index');
        Route::get('colorings/create', [ProductionColoringController::class, 'create'])->name('colorings.create');
        Route::post('colorings', [ProductionColoringController::class, 'store'])->name('colorings.store');
        Route::get('colorings/{coloringBatch}', [ProductionColoringController::class, 'show'])->name('colorings.show')->whereNumber('coloringBatch');
        Route::post('colorings/{coloringBatch}/report', [ProductionColoringController::class, 'storeReport'])->name('colorings.store-report');

        // Proses Finishing (Celup / Molen / Kerok / Bor dengan urutan bebas -> Barang Jadi Siap Jual)
        Route::get('finishings', [ProductionFinishingController::class, 'index'])->name('finishings.index');
        Route::get('finishings/create', [ProductionFinishingController::class, 'create'])->name('finishings.create');
        Route::post('finishings', [ProductionFinishingController::class, 'store'])->name('finishings.store');
        Route::get('finishings/{finishingSession}', [ProductionFinishingController::class, 'show'])->name('finishings.show')->whereNumber('finishingSession');
        Route::post('finishings/{finishingSession}/steps', [ProductionFinishingController::class, 'storeStep'])->name('finishings.store-step')->whereNumber('finishingSession');
        Route::post('finishings/{finishingSession}/complete', [ProductionFinishingController::class, 'complete'])->name('finishings.complete')->whereNumber('finishingSession');

    });

    // Modul STIN (Divisi Khusus)
    Route::middleware('role:stin')->prefix('stin')->name('stin.')->group(function () {
        Route::get('/', [StinController::class, 'dashboard'])->name('dashboard');
        Route::get('items', [StinItemController::class, 'index'])->name('items.index');
        Route::get('items/create', [StinItemController::class, 'create'])->name('items.create');
        Route::post('items', [StinItemController::class, 'store'])->name('items.store');
        Route::get('items/{item}/edit', [StinItemController::class, 'edit'])->name('items.edit');
        Route::put('items/{item}', [StinItemController::class, 'update'])->name('items.update');
        Route::get('items/{item}/mutations', [StinItemController::class, 'mutations'])->name('items.mutations');
        Route::get('items/{item}/mutations/export', [StinItemController::class, 'exportItemMutations'])->name('items.mutations.export');
        Route::get('mutations', [StinMutationController::class, 'index'])->name('mutations.index');
        Route::get('mutations/export', [StinMutationController::class, 'export'])->name('mutations.export');
        Route::get('mutations/{movement}', [StinMutationController::class, 'show'])->name('mutations.show');
        Route::get('items/{item}/print-sku', [StinItemController::class, 'printSku'])->name('items.print-sku');
        Route::get('scanner', [StinScannerController::class, 'index'])->name('scanner.index');
        Route::post('scanner/lookup', [StinScannerController::class, 'lookup'])->name('scanner.lookup');
        Route::post('scanner/action', [StinScannerController::class, 'action'])->name('scanner.action');
        Route::post('scanner/batch-action', [StinScannerController::class, 'batchAction'])->name('scanner.batch-action');
        Route::get('loans', [StinLoanController::class, 'index'])->name('loans.index');
        Route::get('loans/create', [StinLoanController::class, 'create'])->name('loans.create');
        Route::post('loans', [StinLoanController::class, 'store'])->name('loans.store');
        Route::get('loans/{loan}', [StinLoanController::class, 'show'])->name('loans.show');
        Route::get('loans/{loan}/return', [StinLoanController::class, 'returnForm'])->name('loans.return');
        Route::post('loans/{loan}/return', [StinLoanController::class, 'processReturn'])->name('loans.return.process');
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
