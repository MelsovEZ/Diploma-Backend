<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout']);
Route::get('/user', [\App\Http\Controllers\Api\UserController::class, 'getUser'])->middleware('auth:sanctum');

Route::get('/run-command', function () {
    Artisan::call('l5-swagger:generate');
    return "Swagger documentation generated!";
});


Route::get('/home', [\App\Http\Controllers\api\HomeController::class, 'index'])->middleware('auth:sanctum');
