<?php

use Illuminate\Support\Facades\Route;
/*
Route::get('/', function () {
    return view('welcome');
});*/

// Rota para teste de componentes
Route::view('/components-preview', 'preview.components-preview')
    ->name('components.preview');
