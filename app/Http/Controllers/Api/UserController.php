<?php
namespace App\Http\Controllers\Api;

use App\Filters\SearchQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

    /**
     * @OA\Post(
     *     path="/api/user",
     *     summary="Update authenticated user's information",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="surname", type="string", example="Doe"),
     *                 @OA\Property(
     *                     property="photo",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary"),
     *                     description="Optional photo upload (multiple files allowed)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully updated user information",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */


    public function updateUser(UpdateUserRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($request->hasFile('photo')) {
            if ($user->photo_url) {
                $oldPhotoPath = parse_url($user->photo_url, PHP_URL_PATH);
                $oldPhotoPath = ltrim($oldPhotoPath, '/');
                $oldPhotoPath = str_replace('diploma-bucket/', '', $oldPhotoPath);
                if (Storage::disk('s3')->exists($oldPhotoPath)) {
                    Storage::disk('s3')->delete($oldPhotoPath);
                }
            }

            $photo = $request->file('photo');
            $path = Storage::disk('s3')->put("Avatar/User_{$user->id}", $photo, 'public');
            $photoUrl = Storage::disk('s3')->url($path);

            $user->update([
                'name' => $request->name ?? $user->name,
                'surname' => $request->surname ?? $user->surname,
                'photo_url' => $photoUrl,
            ]);
        } else {
            $user->update([
                'name' => $request->name ?? $user->name,
                'surname' => $request->surname ?? $user->surname,
            ]);
        }

        return response()->json(new UserResource($user));
    }


    /**
     * @OA\Delete(
     *     path="/api/user/photo",
     *     summary="Delete authenticated user's profile photo",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully deleted user photo",
     *         @OA\JsonContent(ref="#/components/schemas/UserResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function deletePhoto(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->photo_url) {
            return response()->json(['message' => 'No photo to delete'], 400);
        }

        $photoPath = parse_url($user->photo_url, PHP_URL_PATH);
        $photoPath = ltrim($photoPath, '/');
        $photoPath = str_replace("diploma-bucket/", "", $photoPath);

        if (Storage::disk('s3')->exists($photoPath)) {
            Storage::disk('s3')->delete($photoPath);
        }

        $user->update(['photo_url' => null]);

        return response()->json(new UserResource($user));
    }

    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Get all users",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search users",
     *         required=false,
     *         @OA\Schema(type="string", example="John")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/UserResource"))
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function getAllUsers(Request $request): JsonResponse
    {
        $query = User::query();

        $query = SearchQuery::apply($query, $request, ['name', 'surname', 'email']);

        $users = $query->get();

        return response()->json(UserResource::collection($users));
    }

    /**
     * @OA\Post(
     *     path="/api/user/change-password",
     *     summary="Смена пароля пользователя",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Укажите старый и новый пароль",
     *         @OA\JsonContent(
     *             required={"old_password", "new_password", "new_password_confirmation"},
     *             @OA\Property(property="old_password", type="string", example="oldpassword123", description="Старый пароль"),
     *             @OA\Property(property="new_password", type="string", example="newpassword456", description="Новый пароль"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="newpassword456", description="Подтверждение нового пароля")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Пароль успешно обновлён",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Пароль успешно обновлён")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Старый пароль неверный",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Старый пароль неверный")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Предоставленные данные недействительны.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Пользователь не авторизован"
     *     )
     * )
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = auth()->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['message' => 'Старый пароль неверный'], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Пароль успешно обновлён']);
    }

}
