<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', [\App\Http\Controllers\ProblemController::class, 'index']);


Route::get('/home', [\App\Http\Controllers\Api\HomeController::class, 'index']);
