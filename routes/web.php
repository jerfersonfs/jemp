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
    Route::resource('produtos', ProductController::class)->only(['index', 'store', 'update', 'destroy'])->names('products');
    Route::resource('clientes', CustomerController::class)->only(['index', 'store', 'update', 'destroy'])->names('customers');
    Route::view('/consulta-nfe', 'invoice_consult.invoice-consult', ['page' => 'invoice-consult'])->name('invoice-consult.index');
    Route::get('/vendas', [SaleController::class, 'index'])->name('sales.index');
    Route::post('/vendas', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/entradas', [MovementController::class, 'entries'])->name('entrance.index');
    Route::post('/entradas', [MovementController::class, 'storeEntry'])->name('entrance.store');
    Route::get('/saidas', [MovementController::class, 'exits'])->name('exits.index');
    Route::post('/saidas', [MovementController::class, 'storeExit'])->name('exits.store');
    Route::get('/internas', [MovementController::class, 'transfers'])->name('internal.index');
    Route::post('/internas', [MovementController::class, 'storeTransfer'])->name('internal.store');
    Route::view('/relatorios', 'reports.reports', ['page' => 'reports'])->name('reports.index');
});
