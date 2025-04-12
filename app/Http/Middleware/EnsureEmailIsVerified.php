<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Please verify your email address first.'], 400);
        }

        return $next($request);
    }
}
