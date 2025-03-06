<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout']);
Route::get('/user', [\App\Http\Controllers\Api\UserController::class, 'getUser'])->middleware('auth:sanctum');


Route::get('/home', [\App\Http\Controllers\Api\HomeController::class, 'index']);
