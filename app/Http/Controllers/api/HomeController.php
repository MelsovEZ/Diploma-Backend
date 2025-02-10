<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => true,
        ]);
    }
}
