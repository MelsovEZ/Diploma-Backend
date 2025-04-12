<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Redirect;

class VerificationController extends Controller
{
    /**
     * Показать страницу с уведомлением о верификации.
     *
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        return response()->json(['message' => 'Please verify your email address.']);
    }

    /**
     * Верификация электронной почты.
     *
     * @param  int  $id
     * @param  string  $hash
     * @return JsonResponse
     */
    public function verify($id, $hash): JsonResponse
    {
        $user = \App\Models\User::findOrFail($id);

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email is already verified.']);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json(['message' => 'Email verified successfully.']);
    }

    /**
     * Отправить письмо для верификации.
     *
     * @return JsonResponse
     */
    public function resend(): JsonResponse
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email is already verified.']);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification email sent.']);
    }
}

