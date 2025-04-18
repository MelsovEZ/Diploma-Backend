<?php

namespace App\Http\Controllers\Comment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\CommentRequest;
use App\Http\Requests\Comment\CommentUpdateRequest;
use App\Http\Resources\Comment\CommentResource;
use App\Models\Comment\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(name="Comment")
 */
class CommentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/comments/{problem_id}",
     *     summary="Get all comments for a problem",
     *     tags={"Comment"},
     *     @OA\Parameter(
     *         name="problem_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Problem ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of comments",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=8),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Assanali"),
     *                 @OA\Property(property="surname", type="string", nullable=true, example=null),
     *                 @OA\Property(property="photo_url", type="string", nullable=true, example=null),
     *                 @OA\Property(property="problem_id", type="integer", example=35),
     *                 @OA\Property(property="text", type="string", example="haha"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-12 15:49:55"),
     *                 @OA\Property(property="updated_at", type="string", nullable=true, format="date-time", example="null")
     *             )
     *         )
     *     )
     * )
     */


    public function index($problem_id): JsonResponse
    {
        $comments = Comment::where('problem_id', $problem_id)
            ->with('user:id,name,surname,photo_url')
            ->get();

        return response()->json(CommentResource::collection($comments));
    }



    /**
     * @OA\Post(
     *     path="/api/comments",
     *     summary="Create a new comment",
     *     tags={"Comment"},
     *     security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer"),
     *          description="Comment ID"
     *      ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"problem_id", "text"},
     *             @OA\Property(property="problem_id", type="integer", example=12),
     *             @OA\Property(property="text", type="string", example="This is a comment")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Comment created"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */


    public function store(CommentRequest $request): JsonResponse
    {

        $validated = $request->validated();
        $validated['user_id'] = auth()->id();

        $comment = Comment::create(array_merge($validated, ['created_at' => now(), 'updated_at' => null]));

        return response()->json([
            'status' => true,
            'comment' => $comment->text
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/comments/{id}",
     *     summary="Update a comment",
     *     tags={"Comment"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Comment ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"text"},
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="text", type="string", example="Updated comment text")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Comment updated"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Comment not found")
     * )
     */

    public function update(CommentUpdateRequest $request, $id): JsonResponse
    {
        $comment = Comment::findOrFail($id);

        if ($comment->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!is_null($comment->updated_at)) {
            return response()->json(['error' => 'Comment can only be updated once'], 403);
        }

        $comment->update([
            'text' => $request->validated()['text'],
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'comment' => $comment->text
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/comments/{id}",
     *     summary="Delete a comment",
     *     tags={"Comment"},
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Comment ID"
     *     ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"text"},
     *              @OA\Property(property="text", type="string", example="Comment deleted successfully")
     *          )
     *      ),
     *     @OA\Response(response=200, description="Comment deleted"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Comment not found")
     * )
     */

    public function destroy($id): JsonResponse
    {
        $comment = Comment::findOrFail($id);

        if ($comment->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment->delete();
        return response()->json(['message' => 'Comment deleted successfully']);
    }
}
