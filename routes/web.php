<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::view('/', 'jemp', ['page' => 'home'])->name('home');
    Route::get('/produtos', [ProductController::class, 'index'])->name('products.index');
    Route::resource('produtos', ProductController::class)->only(['store', 'update', 'destroy'])->names('products');
    Route::get('/clientes', [CustomerController::class, 'index'])->name('customers.index');
    Route::resource('clientes', CustomerController::class)->only(['store', 'update', 'destroy'])->names('customers');
    Route::get('/consulta-nfe', [SaleController::class, 'invoices'])->name('invoice-consult.index');
    Route::get('/vendas', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/entradas', [MovementController::class, 'entries'])->name('entrance.index');
    Route::post('/entradas', [MovementController::class, 'storeEntry'])->name('entrance.store');
    Route::get('/saidas', [MovementController::class, 'exits'])->name('exits.index');
    Route::post('/saidas', [MovementController::class, 'storeExit'])->name('exits.store');
    Route::get('/internas', [MovementController::class, 'transfers'])->name('internal.index');
    Route::post('/internas', [MovementController::class, 'storeTransfer'])->name('internal.store');
    Route::view('/relatorios', 'reports.reports', ['page' => 'reports'])->name('reports.index');
});
