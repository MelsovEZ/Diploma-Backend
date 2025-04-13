<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserProblemController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Comment\CommentController;
use App\Http\Controllers\Like\LikeController;
use App\Http\Controllers\Problem\ProblemController;
use App\Http\Controllers\Problem\ProblemResourceController;
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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'getUser']);
    Route::post('/user', [UserController::class, 'updateUser']);
    Route::delete('/user/photo', [UserController::class, 'deletePhoto']);

    Route::get('/user/problems', [UserProblemController::class, 'getUserProblems']);
});


Route::get('/problems', [ProblemController::class, 'index']);
Route::get('/problems/{problem_id}/likes', [LikeController::class, 'getProblemLikes']);
Route::get('/problem-resources', [ProblemResourceController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/problems', [ProblemController::class, 'store']);
    Route::get('/problems/{problem}', [ProblemController::class, 'show']);
    Route::post('/problems/{problem}', [ProblemController::class, 'update']);
    Route::delete('/problems/{problem}', [ProblemController::class, 'destroy']);
    Route::post('/problems/{problem_id}/like', [LikeController::class, 'toggleLike']);
    Route::post('/problems/{problem}/delete-photos', [ProblemController::class, 'deleteProblemPhotos']);

    Route::patch('/problems/{problem_id}/status', [ProblemController::class, 'updateStatus']);
});

Route::get('/categories', [CategoryController::class, 'index']);
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
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

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/users/{user_id}/make-moderator', [AdminController::class, 'makeModerator']);
    Route::post('/users/{user_id}/remove-moderator', [AdminController::class, 'removeModerator']);
});

Route::get('/home', [\App\Http\Controllers\Api\HomeController::class, 'index']);
