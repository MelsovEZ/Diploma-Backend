<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="first"),
     *              @OA\Property(property="surname", type="string", nullable=true, example=null),
     *              @OA\Property(property="status", type="string", example="user"),
     *             @OA\Property(property="email", type="string", example="first@mail.ru"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-11T10:36:03.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-11T10:36:03.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function getUser(Request $request): JsonResponse
    {
        return response()->json($request->user()->only([
            'name', 'surname', 'status', 'email', 'created_at', 'updated_at',
        ]));
    }
}

