<?php
namespace App\Http\Controllers\Api;

use App\Filters\SearchQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="surname", type="string", example="Doe")
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

}
