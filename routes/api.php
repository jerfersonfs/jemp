<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceItemController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rotas da API RESTful (Gera automaticamente GET, POST, PUT/PATCH e DELETE)
Route::apiResource('products', ProductController::class);
Route::apiResource('customers', CustomerController::class);
Route::apiResource('inventories', InventoryController::class);
Route::apiResource('invoice-items', InvoiceItemController::class);
Route::apiResource('stock-movements', StockMovementController::class);
Route::apiResource('warehouses', WarehouseController::class);
Route::apiResource('invoices', InvoiceController::class);

// Endpoints de Relatórios e Filtros
Route::prefix('reports')->group(function () {
    Route::get('/upcoming-invoices', [ReportController::class, 'upcomingInvoices']);
    Route::get('/movement-extract', [ReportController::class, 'movementExtract']);
    Route::get('/inventory-valuation', [ReportController::class, 'inventoryValuation']);
});
