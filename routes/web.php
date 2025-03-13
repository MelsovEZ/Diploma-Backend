<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\ProblemController::class, 'index']);

Route::get('/check-swagger', function () {
    return response()->json([
        'exists' => file_exists(public_path('api-docs/api-docs.json')),
        'path' => public_path('api-docs/api-docs.json'),
    ]);
});


Route::get('/home', [\App\Http\Controllers\Api\HomeController::class, 'index']);
