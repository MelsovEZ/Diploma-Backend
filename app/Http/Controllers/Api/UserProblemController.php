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

    /**
     * @OA\Get(
     *     path="/user/problems",
     *     summary="Get paginated list of problems for the authenticated user",
     *     tags={"Problems"},
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search something",
     *         required=false,
     *         @OA\Schema(type="string", example="With photo")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="category_id[]",
     *         in="query",
     *         description="Filter by category IDs (array of integers)",
     *         required=false,
     *         @OA\Schema(type="array", @OA\Items(type="integer"), example={1,2})
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by problem status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending", "in_progress", "done", "declined"}, example="pending")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort order by created_at (asc or desc)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, example="desc")
     *     ),
     *     @OA\Parameter(
     *         name="from_date",
     *         in="query",
     *         description="Filter problems created from this date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-03-01")
     *     ),
     *     @OA\Parameter(
     *         name="to_date",
     *         in="query",
     *         description="Filter problems created up to this date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-03-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="problem_id", type="integer", example=43),
     *                     @OA\Property(property="user_id", type="integer", example=18),
     *                     @OA\Property(property="title", type="string", example="Test problem 3"),
     *                     @OA\Property(property="description", type="string", example="Test description 3"),
     *                     @OA\Property(property="category", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Мусор")
     *                     ),
     *                     @OA\Property(property="city", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Алматы")
     *                     ),
     *                     @OA\Property(property="district", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Алатауский")
     *                     ),
     *                     @OA\Property(property="status", type="string", enum={"pending", "in_progress", "done", "declined"}, example="pending"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-17T09:14:54.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-17T09:14:54.000000Z"),
     *                     @OA\Property(property="photo_urls", type="array",
     *                         @OA\Items(type="string", example="https://s3.fr-par.scw.cloud/diploma-bucket/problems/User_1/problem_31/a7HJRy3SJpAySlBz5buSU78lsB4phc0dpfu9IjiC.jpg")
     *                     ),
     *                     @OA\Property(property="liked_by_user", type="boolean", example=true),
     *                     @OA\Property(property="likes_count", type="integer", example=0),
     *                     @OA\Property(property="comments_count", type="integer", example=0),
     *                     @OA\Property(property="user", type="object",
     *                         @OA\Property(property="name", type="string", example="IS25"),
     *                         @OA\Property(property="surname", type="string", nullable=true, example=null),
     *                         @OA\Property(property="email", type="string", example="210103203@stu.sdu.edu.kz"),
     *                         @OA\Property(property="photo_url", type="string", nullable=true, example=null)
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="links", type="object",
     *                 @OA\Property(property="first", type="string", example="http://localhost:8000/api/problems?page=1"),
     *                 @OA\Property(property="last", type="string", example="http://localhost:8000/api/problems?page=5"),
     *                 @OA\Property(property="prev", type="string", nullable=true, example=null),
     *                 @OA\Property(property="next", type="string", nullable=true, example="http://localhost:8000/api/problems?page=2")
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
            ->filter($request)
            ->where('user_id', auth()->id());

        $statusCounts = Problem::where('user_id', auth()->id())
            ->selectRaw('
                    count(*) as all_problems_count,
                    sum(CASE WHEN status = \'done\' THEN 1 ELSE 0 END) as done_problems_count,
                    sum(CASE WHEN status = \'in_progress\' THEN 1 ELSE 0 END) as in_progress_problems_count,
                    sum(CASE WHEN status = \'in_review\' THEN 1 ELSE 0 END) as in_review_problems_count
                ')
            ->first();


        $sortOrder = $request->input('sort', 'desc');
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $problems = $query->orderBy('created_at', $sortOrder)->paginate(10);

        // Добавляем в метаданные количество проблем по статусам
        $problems->appends($request->all());

        return ProblemResource::collection($problems)->additional([
            'meta' => [
                'all_problems_count' => $statusCounts->all_problems_count,
                'done_problems_count' => $statusCounts->done_problems_count,
                'in_progress_problems_count' => $statusCounts->in_progress_problems_count,
                'in_review_problems_count' => $statusCounts->in_review_problems_count,
            ]
        ]);
    }
}
