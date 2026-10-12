<?php

use Illuminate\Support\Facades\Route;
/*
Route::get('/', function () {
    return view('welcome');
});*/

// Home e páginas existentes do protótipo.
Route::view('/', 'jemp', ['page' => 'home'])->name('home');
Route::view('/login', 'login')->name('login');
Route::view('/produtos', 'products.products', ['page' => 'products'])->name('products.index');
Route::view('/clientes', 'customers.customers', ['page' => 'customers'])->name('customers.index');
Route::view('/consulta-nfe', 'invoice_consult.invoice-consult', ['page' => 'invoice-consult'])->name('invoice-consult.index');
Route::view('/vendas', 'sales.sales', ['page' => 'sales'])->name('sales.index');
Route::view('/entradas', 'entrance.entrance', ['page' => 'entrance'])->name('entrance.index');
Route::view('/saidas', 'exits.exits', ['page' => 'exits'])->name('exits.index');
Route::view('/internas', 'internal.internal', ['page' => 'internal'])->name('internal.index');
Route::view('/relatorios', 'reports.reports', ['page' => 'reports'])->name('reports.index');
