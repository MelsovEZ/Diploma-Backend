<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\api\HomeController::class, 'index']);


Route::get('/home', [App\Http\Controllers\HomeController::class, 'index']);
