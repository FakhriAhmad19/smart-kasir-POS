<?php

use App\Http\Controllers\CafeTableController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ModifierGroupController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RawMaterialController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::get('/home', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')
        ->middleware('can:view-dashboard');

    // My Profile (available to every authenticated user)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // POS Cashier Interface (require open shift for main actions)
    Route::middleware('can:process-transaction')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index')
            ->middleware('ensure.shift.open');
        Route::get('/pos/search', [PosController::class, 'search'])->name('pos.search')
            ->middleware('ensure.shift.open');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout')
            ->middleware('ensure.shift.open');
        Route::get('/pos/receipt/{transaction}', [PosController::class, 'showReceipt'])->name('pos.receipt');
        Route::get('/pos/stocks', [PosController::class, 'stocks'])->name('pos.stocks');

        // Open bills (bayar nanti)
        Route::middleware('ensure.shift.open')->prefix('pos/bills')->name('pos.bills.')->group(function () {
            Route::get('/', [PosController::class, 'openBills'])->name('index');
            Route::post('/', [PosController::class, 'storeBill'])->name('store');
            Route::get('/{bill}', [PosController::class, 'showBill'])->name('show');
            Route::post('/{bill}/items', [PosController::class, 'addBillItems'])->name('items');
            Route::post('/{bill}/pay', [PosController::class, 'payBill'])->name('pay');
            Route::post('/{bill}/cancel', [PosController::class, 'cancelBill'])->name('cancel');
        });
    });

    // Shift Management
    Route::middleware('can:manage-shifts')->group(function () {
        Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
        Route::get('/shifts/open', [ShiftController::class, 'openForm'])->name('shifts.open_form');
        Route::post('/shifts/open', [ShiftController::class, 'open'])->name('shifts.open');
        Route::get('/shifts/close', [ShiftController::class, 'closeForm'])->name('shifts.close_form');
        Route::post('/shifts/close', [ShiftController::class, 'close'])->name('shifts.close');
    });

    // Category Management
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit'])
        ->middleware('can:manage-categories');

    // Product Management
    Route::resource('products', ProductController::class)->except(['show'])
        ->middleware('can:manage-products');

    Route::resource('raw-materials', RawMaterialController::class)->except(['show'])
        ->parameters(['raw-materials' => 'rawMaterial'])
        ->middleware('can:manage-products');

    Route::get('/tickets/{station}', [TicketController::class, 'index'])
        ->whereIn('station', ['barista', 'kitchen'])
        ->name('tickets.index')
        ->middleware('can:process-transaction');

    // Menu Modifiers & Variants (size, sugar level, toppings, ...)
    Route::resource('modifiers', ModifierGroupController::class)->except(['show'])
        ->middleware('can:manage-products');

    // Cafe Tables (dine-in orders are delivered to a table)
    Route::middleware('can:manage-products')->group(function () {
        Route::post('/tables/bulk', [CafeTableController::class, 'bulkStore'])->name('tables.bulk');
        Route::resource('tables', CafeTableController::class)->only(['index', 'store', 'update', 'destroy']);
    });

    // Stock In (Supplier Restock)
    Route::resource('stock-ins', StockInController::class)->only(['index', 'create', 'store'])
        ->middleware('can:manage-stock-in');

    // Stock Opname & Adjustments
    Route::resource('adjustments', StockAdjustmentController::class)->only(['index', 'create', 'store'])
        ->middleware('can:manage-adjustments');

    // Operational Expenses
    Route::resource('expenses', ExpenseController::class)->only(['index', 'store', 'destroy'])
        ->middleware('can:manage-expenses');

    // Reports & Reconciliation
    Route::middleware('can:view-financial-reports')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/reconciliation', [ReportController::class, 'reconciliation'])->name('reports.reconciliation');
        Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export_csv');
        Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export_pdf');
        Route::get('/reports/shift-pdf/{shift}', [ReportController::class, 'exportShiftPdf'])->name('reports.shift_pdf');
    });

    // User Management
    Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy'])
        ->middleware('can:manage-users');

    // Store Settings
    Route::middleware('can:manage-settings')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
