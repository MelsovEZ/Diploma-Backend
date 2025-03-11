<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\ProblemController::class, 'index']);

Route::get('/home', [\App\Http\Controllers\Api\HomeController::class, 'index']);
