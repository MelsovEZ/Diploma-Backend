<?php

namespace App\Http\Controllers\Like;

use App\Models\Likes\Like;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class LikeController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/problems/{problem_id}/like",
     *     summary="Toggle like on a problem",
     *     security={{"sanctum": {}}},
     *     tags={"Likes"},
     *     @OA\Parameter(
     *         name="problem_id",
     *         in="path",
     *         required=true,
     *         description="Problem ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Like toggled successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Liked successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function toggleLike(Request $request, $problem_id): JsonResponse
    {
        $user_id = auth()->id();

        $like = Like::where('user_id', $user_id)->where('problem_id', $problem_id);

        if ($like->exists()) {
            $like->delete();
            return response()->json(['message' => 'Unliked successfully']);
        } else {
            Like::create([
                'user_id' => $user_id,
                'problem_id' => $problem_id
            ]);
            return response()->json(['message' => 'Liked successfully']);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/problems/{problem_id}/likes",
     *     summary="Get the list of users who liked a problem",
     *     tags={"Likes"},
     *     @OA\Parameter(
     *         name="problem_id",
     *         in="path",
     *         required=true,
     *         description="Problem ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of users and like count",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="likes_count", type="integer", example=2),
     *             @OA\Property(
     *                 property="users",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="surname", type="string", nullable=true),
     *                     example={
     *                         {"id": 1, "name": "Assanali", "surname": null},
     *                         {"id": 4, "name": "Aahah", "surname": "Ohooh"}
     *                     }
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function getProblemLikes($problem_id): JsonResponse
    {
        $likes = Like::where('problem_id', $problem_id)
            ->with('user:id,name,surname')
            ->get();

        return response()->json([
            'likes_count' => $likes->count(),
            'users' => $likes->pluck('user')
        ]);
    }
}
