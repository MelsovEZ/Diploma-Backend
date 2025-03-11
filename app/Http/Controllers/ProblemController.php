<?php

namespace App\Http\Controllers;

use App\Models\Problem;
use Illuminate\Http\Request;
use App\Http\Requests\ProblemRequest;
use App\Http\Requests\ProblemUpdateRequest;

/**
 * @OA\Tag(name="Problems")
 */
class ProblemController extends Controller
{
    /**
     * @OA\Get(
     *     path="/problems",
     *     summary="Get all problems",
     *     tags={"Problems"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="problem_id", type="integer", example=6),
     *                 @OA\Property(property="user_id", type="integer", example=2),
     *                 @OA\Property(property="title", type="string", example="Second Problem"),
     *                 @OA\Property(property="description", type="string", example="This is a test problem"),
     *                 @OA\Property(property="category_id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", enum={"pending", "in_progress", "done", "declined"}, example="pending"),
     *                 @OA\Property(property="location_lat", type="string", example="51.1657000"),
     *                 @OA\Property(property="location_lng", type="string", example="10.4515000"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-11T13:52:04.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-11T13:52:04.000000Z")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        return Problem::all();
    }


    /**
     * @OA\Post(
     *     path="/problems",
     *     summary="Create a new problem",
     *     tags={"Problems"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "description", "category_id", "location_lat", "location_lng"},
     *             @OA\Property(property="title", type="string", example="Broken streetlight"),
     *             @OA\Property(property="description", type="string", example="The streetlight is broken on 5th avenue."),
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="location_lat", type="number", format="float", example=40.7128),
     *             @OA\Property(property="location_lng", type="number", format="float", example=-74.0060)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Problem created successfully"
     *     )
     * )
     */
    public function store(ProblemRequest $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';

        $problem = Problem::create($validated);

        return response()->json([
            'status' => true,
            'problem' => $problem
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/problems/{id}",
     *     summary="Get a specific problem",
     *     tags={"Problems"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the problem",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function show(Problem $problem)
    {
        return $problem;
    }

    /**
     * @OA\Put(
     *     path="/problems/{id}",
     *     summary="Update a problem",
     *     tags={"Problems"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the problem",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated title"),
     *             @OA\Property(property="description", type="string", example="Updated description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Problem updated successfully"
     *     )
     * )
     */
    public function update(ProblemUpdateRequest $request, Problem $problem)
    {
        if ($problem->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($problem->status !== 'pending') {
            return response()->json(['message' => 'You can only edit problems with pending status'], 403);
        }

        $problem->update($request->validated());

        return response()->json([
            'status' => true,
            'problem' => $problem
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/problems/{id}",
     *     summary="Delete a problem",
     *     tags={"Problems"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the problem",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Problem deleted successfully"
     *     )
     * )
     */
    public function destroy(Problem $problem)
    {
        if ($problem->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $problem->delete();
        return response()->json(['message' => 'Problem deleted successfully']);
    }
}
