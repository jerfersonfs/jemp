<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'jemp', ['page' => 'home'])->name('home');
Route::view('/login', 'login')->name('login');
Route::view('/catalogo', 'jemp', ['page' => 'catalogo'])->name('catalogo');
Route::view('/produtos/novo', 'jemp', ['page' => 'produto'])->name('produtos.create');
Route::view('/inventario', 'jemp', ['page' => 'inventario'])->name('inventario');
Route::view('/saidas-nfe', 'jemp', ['page' => 'saidas'])->name('saidas');
