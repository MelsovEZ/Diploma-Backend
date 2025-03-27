<?php
namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/users/{id}/make-moderator",
     *     summary="Promote user to moderator",
     *     tags={"Admin"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User promoted to moderator",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User promoted to moderator")
     *         )
     *     ),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=403, description="Admin cannot be changed")
     * )
     */
    public function makeModerator($id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->status === 'admin') {
            return response()->json(['error' => 'Admin cannot be changed'], 403);
        }

        $user->update(['status' => 'moderator']);
        return response()->json(['message' => 'User promoted to moderator']);
    }

    /**
     * @OA\Post(
     *     path="/api/users/{id}/remove-moderator",
     *     summary="Remove moderator role from user",
     *     tags={"Admin"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Moderator role removed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Moderator role removed")
     *         )
     *     ),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=400, description="User is not a moderator")
     * )
     */
    public function removeModerator($id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->status !== 'moderator') {
            return response()->json(['error' => 'User is not a moderator'], 400);
        }

        $user->update(['status' => 'user']);
        return response()->json(['message' => 'Moderator role removed']);
    }
}
