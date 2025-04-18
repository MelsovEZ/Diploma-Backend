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

        if ($user->status === 'moderator') {
            if ($this->isModeratorTaskRoute($request)) {
                return $next($request);
            }

            return response()->json(['message' => 'You don\'t have permission!'], 403);
        }

        return response()->json(['message' => 'You don`t have permission!'], 403);
    }

    private function isModeratorTaskRoute(Request $request): bool
    {
        return $request->is('api/problems/*/report') ||
            $request->is('api/problems/*/resolve') ||
            $request->is('api/problems/*/submit-resolution') ||
            $request->is('api/problems/*/report/update');
    }
}
