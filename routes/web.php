<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\Api\HomeController::class, 'index']);


Route::get('/home', [\App\Http\Controllers\Api\HomeController::class, 'index']);
