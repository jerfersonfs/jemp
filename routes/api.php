<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceItemController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Os nomes recebem prefixo próprio para não sobrescrever os nomes das rotas web.
Route::name('api.')->group(function (): void {
    Route::post('/inventories/remove-stock', [MovementController::class, 'storeExit'])
        ->name('inventories.remove-stock');
    Route::apiResource('products', ProductController::class)->parameters(['products' => 'produto']);
    Route::apiResource('customers', CustomerController::class)->parameters(['customers' => 'cliente']);
    Route::apiResource('inventories', InventoryController::class);
    Route::apiResource('invoice-items', InvoiceItemController::class);
    Route::apiResource('stock-movements', StockMovementController::class);
    Route::apiResource('warehouses', WarehouseController::class);
    Route::apiResource('invoices', InvoiceController::class)->except(['update', 'destroy']);
});

// Endpoints de Relatórios e Filtros
Route::prefix('reports')->group(function () {
    Route::get('/upcoming-invoices', [ReportController::class, 'upcomingInvoices']);
    Route::get('/movement-extract', [ReportController::class, 'movementExtract']);
    Route::get('/inventory-valuation', [ReportController::class, 'inventoryValuation']);
});
