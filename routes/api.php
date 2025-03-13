<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\ProblemController;
use App\Http\Controllers\CategoryController;

Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout']);
Route::get('/user', [\App\Http\Controllers\Api\UserController::class, 'getUser'])->middleware('auth:sanctum');

Route::get('/problems', [ProblemController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/problems', [ProblemController::class, 'store']);
    Route::get('/problems/{problem}', [ProblemController::class, 'show']);
    Route::put('/problems/{problem}', [ProblemController::class, 'update']);
    Route::delete('/problems/{problem}', [ProblemController::class, 'destroy']);
});

Route::get('/categories', [CategoryController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
});


Route::get('/home', [\App\Http\Controllers\Api\HomeController::class, 'index']);
