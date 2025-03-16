<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comments\CommentRequest;
use App\Http\Requests\Comments\CommentUpdateRequest;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(name="Comments")
 */
class CommentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/comments/{problem_id}",
     *     summary="Get all comments for a problem",
     *     tags={"Comments"},
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
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=2),
     *                 @OA\Property(property="problem_id", type="integer", example=3),
     *                 @OA\Property(property="text", type="string", example="This is a comment"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-14T10:00:00.000Z"),
     *                  @OA\Property(property="updated_at", type="string", format="date-time", example="null")
     *             )
     *         )
     *     )
     * )
     */

    public function index($problem_id): JsonResponse
    {
        return response()->json(Comment::where('problem_id', $problem_id)->get());
    }

    /**
     * @OA\Post(
     *     path="/api/comments",
     *     summary="Create a new comment",
     *     tags={"Comments"},
     *     security={{ "sanctum": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"problem_id", "text"},
     *             @OA\Property(property="status", type="boolean", example=true),
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
     *     tags={"Comments"},
     *     security={{ "sanctum": {} }},
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
     *     tags={"Comments"},
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
