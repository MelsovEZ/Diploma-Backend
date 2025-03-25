<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthRequest;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/user",
     *     summary="Get authenticated user",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function getUser(AuthRequest $request): JsonResponse
    {
        Log::info('Bearer Token:', ['token' => $request->bearerToken()]);

        if (!$request->user()) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        return response()->json(new UserResource($request->user()));
    }
}
