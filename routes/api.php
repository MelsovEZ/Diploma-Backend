<?php

use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Comment\CommentController;
use App\Http\Controllers\Problem\ProblemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;


Log::info('Incoming request', [
    'method' => request()->method(),
    'url' => request()->fullUrl(),
    'headers' => request()->headers->all(),
    'body' => request()->all(),
]);

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

Route::get('/comments/{problem_id}', [CommentController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{comment_id}', [CommentController::class, 'destroy']);
    Route::put('/comments/{comment_id}', [CommentController::class, 'update']);
});


Route::post('/upload', function (Request $request) {
    try {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        // Проверка, что файл успешно получен
        if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
            Log::error('Invalid file in request');
            return response()->json(['error' => 'Invalid file uploaded'], 400);
        }

        Log::info('File received', [
            'original_name' => $request->file('file')->getClientOriginalName(),
            'mime_type' => $request->file('file')->getMimeType(),
            'size' => $request->file('file')->getSize()
        ]);

        // Попробуем получить ошибку
        try {
            $path = $request->file('file')->store('uploads', 's3');
            Log::info('Upload attempt result', ['path' => $path]);
        } catch (\Exception $e) {
            Log::error('S3 upload exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'S3 upload exception: ' . $e->getMessage()], 500);
        }

        if (!$path) {
            Log::error('Upload failed with no exception');
            return response()->json(['error' => 'File upload failed'], 500);
        }

        // Остальной код без изменений
        if (!Storage::disk('s3')->exists($path)) {
            return response()->json(['error' => 'File not found after upload'], 500);
        }

        $fileUrl = Storage::disk('s3')->url($path);
        Log::info('File uploaded successfully', ['path' => $path, 'url' => $fileUrl]);

        return response()->json([
            'message' => 'File uploaded successfully!',
            'path' => $fileUrl,
        ]);
    } catch (\Exception $e) {
        Log::error('Global exception', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
    }
});

Route::get('/home', [\App\Http\Controllers\Api\HomeController::class, 'index']);
