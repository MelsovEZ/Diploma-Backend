<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($user->status === 'admin') {
            return $next($request);
        }

        if ($user->status === 'moderator' && !$this->isModeratorManagementRoute($request)) {
            return $next($request);
        }

        return response()->json(['message' => 'You don`t have permission!'], 403);
    }

    private function isModeratorManagementRoute(Request $request): bool
    {
        return $request->is('api/users/*/make-moderator') || $request->is('api/users/*/remove-moderator');
    }
}
