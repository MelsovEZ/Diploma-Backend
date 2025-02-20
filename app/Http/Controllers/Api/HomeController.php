<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Get(
 *     path="/api/home",
 *     summary="Home API",
 *     description="Description of API.",
 *     tags={"Home"},
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Welcome to API")
 *         )
 *     )
 * )
 */

class HomeController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Welcome to the Home API AHAHAHAHA!'
        ]);
    }

}
