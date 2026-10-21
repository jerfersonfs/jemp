<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
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
    Route::view('/vendas', 'sales.sales', ['page' => 'sales'])->name('sales.index');
    Route::view('/entradas', 'entrance.entrance', ['page' => 'entrance'])->name('entrance.index');
    Route::view('/saidas', 'exits.exits', ['page' => 'exits'])->name('exits.index');
    Route::view('/internas', 'internal.internal', ['page' => 'internal'])->name('internal.index');
    Route::view('/relatorios', 'reports.reports', ['page' => 'reports'])->name('reports.index');
});
