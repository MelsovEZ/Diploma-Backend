<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\ProblemController;

Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout']);
Route::get('/user', [\App\Http\Controllers\Api\UserController::class, 'getUser'])->middleware('auth:sanctum');

Route::get('/problems', [ProblemController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/problems', [\App\Http\Controllers\ProblemController::class, 'store']);
    Route::get('/problems/{problem}', [ProblemController::class, 'show']);
    Route::put('/problems/{problem}', [ProblemController::class, 'update']);
    Route::delete('/problems/{problem}', [ProblemController::class, 'destroy']);
});


Route::get('/home', [\App\Http\Controllers\Api\HomeController::class, 'index']);
