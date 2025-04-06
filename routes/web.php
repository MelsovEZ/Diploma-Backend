<?php

use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

Route::get('/email/verify', [VerificationController::class, 'show'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify')->middleware('signed');
Route::post('/email/resend', [VerificationController::class, 'resend'])->name('verification.resend');


Route::get('/test-email', function () {
    Mail::raw('Test email from Laravel', function ($message) {
        $message->to('kaketika009@gmail.com')
            ->subject('Test Email');
    });

    return 'Email sent';
});


Route::get('/', [\App\Http\Controllers\Problem\ProblemController::class, 'index']);


Route::get('/home', [\App\Http\Controllers\Api\HomeController::class, 'index']);
