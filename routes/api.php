<?php

use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Comment\CommentController;
use App\Http\Controllers\Like\LikeController;
use App\Http\Controllers\Problem\ProblemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
/*
Log::info('Incoming request', [
    'method' => request()->method(),
    'url' => request()->fullUrl(),
    'headers' => request()->headers->all(),
    'body' => request()->all(),
]);
*/
Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout']);
Route::get('/user', [\App\Http\Controllers\Api\UserController::class, 'getUser'])->middleware('auth:sanctum');

Route::get('/problems', [ProblemController::class, 'index']);
Route::get('/problems/{problem_id}/likes', [LikeController::class, 'getProblemLikes']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/problems', [ProblemController::class, 'store']);
    Route::get('/problems/{problem}', [ProblemController::class, 'show']);
    Route::put('/problems/{problem}', [ProblemController::class, 'update']);
    Route::delete('/problems/{problem}', [ProblemController::class, 'destroy']);
    Route::post('/problems/{problem_id}/like', [LikeController::class, 'toggleLike']);
});


Route::get('/categories', [CategoryController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
});

Route::get('/comments/{problem_id}', [CommentController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{comment_id}', [CommentController::class, 'destroy']);
    Route::put('/comments/{comment_id}', [CommentController::class, 'update']);
});

Route::get('/home', [\App\Http\Controllers\Api\HomeController::class, 'index']);
