<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Problem\ProblemResource;
use App\Models\Problem\Problem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserProblemController extends Controller
{

    /**
     * @OA\Get(
     *     path="/user/problems",
     *     summary="Get paginated list of problems for the authenticated user",
     *     tags={"Problems"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort order by creation date (asc or desc)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, example="desc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="problem_id", type="integer", example=31),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Example title"),
     *                     @OA\Property(property="description", type="string", example="Example description"),
     *                     @OA\Property(property="category_id", type="integer", example=1),
     *                     @OA\Property(property="status", type="string", enum={"pending", "in_progress", "done", "declined"}, example="pending"),
     *                     @OA\Property(property="location_lat", type="number", format="float", example=59.333),
     *                     @OA\Property(property="location_lng", type="number", format="float", example=20.333),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-19T14:44:17.000000Z"),
     *                     @OA\Property(
     *                         property="photo_urls",
     *                         type="array",
     *                         @OA\Items(
     *                             type="string",
     *                             example="https://example.com/photo.jpg"
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="links", type="object",
     *                 @OA\Property(property="first", type="string", example="http://localhost:8000/api/user/problems?page=1"),
     *                 @OA\Property(property="last", type="string", example="http://localhost:8000/api/user/problems?page=5"),
     *                 @OA\Property(property="prev", type="string", nullable=true, example=null),
     *                 @OA\Property(property="next", type="string", nullable=true, example="http://localhost:8000/api/user/problems?page=2")
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="to", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=50)
     *             )
     *         )
     *     )
     * )
     */
    public function getUserProblems(Request $request): AnonymousResourceCollection
    {
        $query = Problem::with('photos:problem_id,photo_url')
            ->select(['problem_id', 'user_id', 'title', 'description', 'category_id', 'status', 'location_lat', 'location_lng', 'created_at'])
            ->where('user_id', auth()->id());

        $sortOrder = $request->input('sort', 'desc');
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        return ProblemResource::collection(
            $query->orderBy('created_at', $sortOrder)->paginate(10)
        );
    }
}
