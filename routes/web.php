<?php

use Illuminate\Support\Facades\Route;
/*
Route::get('/', function () {
    return view('welcome');
});*/

// Rota para teste de componentes
Route::get('/components-preview', function () {
    return view('components-preview');
});
   