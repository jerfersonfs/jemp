<?php

use Illuminate\Support\Facades\Route;

// Rota inicial
Route::get('/', function () {
    return redirect('/login');
});

// Rotas de Autenticação
Route::get('/login', [\App\Http\Controllers\AuthController::class, 'index'])->name('login');
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login'])->name('login.post');

// Rotas Protegidas
Route::middleware('auth')->group(function () {

    Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function () {
        return 'Bem-vindo ao JEMP! Você está logado.';
    })->name('dashboard');
// CRUD de Produtos
    Route::resource('produtos', \App\Http\Controllers\ProductController::class);
});
