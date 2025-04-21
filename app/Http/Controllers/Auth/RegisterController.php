<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Symfony\Component\Mime\Part\TextPart;


class RegisterController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Auth"},
     *     summary="User Registration",
     *     description="Register a new user and send a verification code to email",
     *     operationId="registerUser",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *             @OA\Property(property="name", type="string", example="JohnDoe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verification code sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Письмо с кодом отправлено на ваш email. Пожалуйста, проверьте также папку 'Спам'.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="Имя не должно содержать пробелы.")
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(type="string", example="Эта почта уже занята.")
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="array",
     *                     @OA\Items(type="string", example="Пароли не совпадают.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function register(RegisterRequest $request): JsonResponse
    {
        $verificationCode = rand(100000, 999999);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'code' => $verificationCode
        ];

        Cache::put('register_' . $request->email, $data, now()->addMinutes(10));

        Mail::send('auth.verify-code', [
            'code' => $verificationCode,
        ], function ($message) use ($verificationCode, $request) {
            $htmlPart = new TextPart(
                view('auth.verify-code', [
                    'code' => $verificationCode
                ])->render(),
                'utf-8',
                'html'
            );

            $message->to($request->email)
                ->subject('Код подтверждения для регистрации')
                ->setBody($htmlPart);
        });


        return response()->json([
            'message' => 'Письмо с кодом отправлено на ваш email. Пожалуйста, проверьте также папку Спам.'
        ], 200);
    }


    /**
     * @OA\Post(
     *     path="/api/verify-email",
     *     tags={"Auth"},
     *     summary="Verify Email with Code",
     *     description="Verify email address with the sent code and register the user",
     *     operationId="verifyEmail",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "verification_code"},
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="verification_code", type="string", example="123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User successfully registered",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Пользователь успешно зарегистрирован."),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john.doe@example.com")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid verification code",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Неверный код верификации.")
     *         )
     *     )
     * )
     */

    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required|size:6',
        ]);

        $cachedData = Cache::get('register_' . $request->email);

        if (!$cachedData || $cachedData['code'] !== $request->verification_code) {
            return response()->json([
                'message' => 'Неверный код верификации.'
            ], 422);
        }

        $user = User::create([
            'name' => $cachedData['name'],
            'email' => $cachedData['email'],
            'password' => Hash::make($cachedData['password']),
        ]);

        Cache::forget('register_' . $request->email);

        return response()->json([
            'message' => 'Пользователь успешно зарегистрирован.',
            'user' => $user
        ], 201);
    }


}
