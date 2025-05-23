<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Login method for API
     *
     * @param Request $request
     * @return JsonResponse
     */


    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Вход пользователя",
     *     description="Вход пользователя и получение токена доступа",
     *     operationId="loginUser",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Вход выполнен успешно",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Вход выполнен успешно."),
     *             @OA\Property(property="token", type="string", example="1|abc123...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Неавторизован",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Логин или пароль неверны.")
     *         )
     *     )
     * )
     */


    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'Логин или пароль неверны.'
            ], 401);
        }


        $token = $user->createToken('YourAppName')->plainTextToken;

        return response()->json([
            'message' => 'Вход выполнен успешно.',
            'token' => $token
        ], 200);
    }

    /**
     * Logout method for API
     *
     * @param Request $request
     * @return JsonResponse
     */

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Auth"},
     *     summary="User Logout",
     *     description="Logout a user and revoke the access token",
     *     operationId="logoutUser",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $user->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

}
