<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceItemController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/products', [ProductController::class, 'index'])
    ->name('products.index');

Route::get('/customers', [CustomerController::class, 'index'])
    ->name('customers.index');

Route::get('/inventories', [InventoryController::class, 'index'])
    ->name('inventories.index');

Route::get('/invoice-items', [InvoiceItemController::class, 'index'])
    ->name('invoice-items.index');

Route::get('/invoices', [InvoiceController::class, 'index'])
    ->name('invoices.index');

Route::get('/stock-movements', [StockMovementController::class, 'index'])
    ->name('stock-movements.index');

Route::get('/warehouses', [WarehouseController::class, 'index'])
    ->name('warehouses.index');
