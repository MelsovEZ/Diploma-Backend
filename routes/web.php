<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', [\App\Http\Controllers\ProblemController::class, 'index']);

Route::get('/test-s3', function () {
    try {
        $filePath = 'test.txt';
        Storage::disk('s3')->put($filePath, 'Hello, Scaleway!');

        $exists = Storage::disk('s3')->exists($filePath);

        return response()->json(['success' => true, 'file_exists' => $exists]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});



Route::get('/home', [\App\Http\Controllers\Api\HomeController::class, 'index']);
