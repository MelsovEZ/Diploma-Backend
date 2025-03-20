<?php

namespace App\Http\Controllers\Problem;

use App\Http\Controllers\Controller;
use App\Http\Requests\Problem\ProblemRequest;
use App\Http\Requests\Problem\ProblemUpdateRequest;
use App\Http\Resources\Problem\ProblemResource;
use App\Models\Problem\Problem;
use App\Models\Problem\ProblemPhoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(name="Problems")
 */
class ProblemController extends Controller
{
    /**
     * @OA\Get(
     *     path="/problems",
     *     summary="Get all problems with their photos",
     *     tags={"Problems"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="problem_id", type="integer", example=31),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="With photo"),
     *                 @OA\Property(property="description", type="string", example="desc"),
     *                 @OA\Property(property="category_id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", enum={"pending", "in_progress", "done", "declined"}, example="pending"),
     *                 @OA\Property(property="location_lat", type="number", format="float", example=59.333),
     *                 @OA\Property(property="location_lng", type="number", format="float", example=20.333),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-19T14:44:17.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-19T14:44:17.000000Z"),
     *                 @OA\Property(
     *                     property="photo_urls",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         example="https://s3.fr-par.scw.cloud/diploma-bucket/problems/User_1/problem_31/a7HJRy3SJpAySlBz5buSU78lsB4phc0dpfu9IjiC.jpg"
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function index(): AnonymousResourceCollection
    {
        return ProblemResource::collection(Problem::with('photos:problem_id,photo_url')->get());
    }

    /**
     * @OA\Post(
     *     path="/problems",
     *     summary="Create a new problem",
     *     tags={"Problems"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "description", "category_id", "location_lat", "location_lng"},
     *                 @OA\Property(property="title", type="string", example="Broken streetlight"),
     *                 @OA\Property(property="description", type="string", example="The streetlight is broken on 5th avenue."),
     *                 @OA\Property(property="category_id", type="integer", example=1),
     *                 @OA\Property(property="location_lat", type="number", format="float", example=40.7128),
     *                 @OA\Property(property="location_lng", type="number", format="float", example=-74.0060),
     *                 @OA\Property(
     *                     property="photos[]",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary"),
     *                     description="Optional multiple photo uploads (1 to 5 images)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Problem created successfully"
     *     )
     * )
     */


    public function store(ProblemRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';

        $problem = Problem::create($validated);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                if ($photo->isValid()) {
                    $userId = auth()->id();
                    $problemId = $problem->problem_id;
                    $path = $photo->store("problems/User_{$userId}/problem_{$problemId}", 's3');
                    Storage::disk('s3')->setVisibility($path, 'public');
                    $photoUrl = Storage::disk('s3')->url($path);

                    ProblemPhoto::create([
                        'problem_id' => $problem->problem_id,
                        'photo_url' => $photoUrl,
                    ]);
                }
            }
        }

        return response()->json([
            'status' => true,
            'problem' => $problem
        ], 201);
    }


    /**
     * @OA\Get(
     *     path="/problems/{id}",
     *     summary="Get a specific problem with its photos",
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
    public function show(Problem $problem): ProblemResource
    {
        return new ProblemResource($problem);
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
    public function update(ProblemUpdateRequest $request, Problem $problem): JsonResponse
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
    public function destroy(Problem $problem): JsonResponse
    {
        if ($problem->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $photos = ProblemPhoto::where('problem_id', $problem->problem_id)->get();
        foreach ($photos as $photo) {
            $filePath = ltrim(parse_url($photo->photo_url, PHP_URL_PATH), '/');
            $filePath = str_replace("diploma-bucket/", "", $filePath);
            if (Storage::disk('s3')->exists($filePath)) {
                Storage::disk('s3')->delete($filePath);
            }
            $photo->delete();
        }

        // Удаляем проблему
        $problem->delete();

        return response()->json(['message' => 'Problem deleted successfully']);
    }
}
