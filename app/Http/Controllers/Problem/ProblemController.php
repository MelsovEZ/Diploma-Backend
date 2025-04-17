<?php

namespace App\Http\Controllers\Problem;

use App\Http\Controllers\Controller;
use App\Http\Requests\Problem\ProblemIndexRequest;
use App\Http\Requests\Problem\ProblemRequest;
use App\Http\Requests\Problem\ProblemUpdateRequest;
use App\Http\Resources\Problem\ProblemResource;
use App\Models\City\City;
use App\Models\District\District;
use App\Models\Problem\Problem;
use App\Models\Problem\ProblemPhoto;
use App\Services\GisService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(name="Problems")
 */
class ProblemController extends Controller
{
    protected GisService $gisService;

    public function __construct(GisService $gisService)
    {
        $this->gisService = $gisService;
    }
    /**
     * @OA\Get(
     *     path="/problems",
     *     summary="Get paginated list of problems with filtering and sorting",
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


    public function index(ProblemIndexRequest $request): AnonymousResourceCollection
    {

        $columns = DB::getSchemaBuilder()->getColumnListing('problems');
        $query = Problem::with([
            'photos:problem_id,photo_url',
            'category:id,name',
            'city:id,name',
            'district:id,name',
            'user:id,name,surname,email,photo_url'
        ])
            ->select($columns)
            ->filter($request)
            ->orderBy('created_at', $request->input('sort', 'desc'));

        return ProblemResource::collection($query->paginate(10));
    }


    /**
     * @OA\Post(
     *     path="/problems",
     *     summary="Create a new problem",
     *     tags={"Problems"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "description", "category_id", "city_id", "district_id", "address"},
     *                 @OA\Property(property="title", type="string", example="Broken streetlight"),
     *                 @OA\Property(property="description", type="string", example="The streetlight is broken on 5th avenue."),
     *                 @OA\Property(property="category_id", type="integer", example=1),
     *                 @OA\Property(property="city_id", type="integer", example=1),
     *                 @OA\Property(property="district_id", type="integer", example=1),
     *                 @OA\Property(property="address", type="string", example="5th avenue, 54"),
     *                 @OA\Property(
     *                     property="photos[]",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary"),
     *                     description="Optional multiple photo uploads (1 to 5 images)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Problem created successfully"),
     *     @OA\Response(response=400, description="Invalid city or district ID"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     * @throws ConnectionException
     */

    public function store(ProblemRequest $request): JsonResponse
    {

        $cityId = $request->input('city_id');
        $districtId = $request->input('district_id');
        $address = $request->input('address');

        $cityExists = City::where('id', $cityId)->exists();
        $districtExists = District::where('id', $districtId)->exists();

        if (!$cityExists) {
            return response()->json([
                'status' => false,
                'message' => 'Город с таким ID не найден.'
            ], 400);
        }

        if (!$districtExists) {
            return response()->json([
                'status' => false,
                'message' => 'Район с таким ID не найден.'
            ], 400);
        }

        $coordinates = $this->gisService->getCoordinatesFromAddress($address, $cityId, $districtId);

        $validated = $request->validated();

        if ($coordinates) {
            $validated['location_lat'] = $coordinates['latitude'];
            $validated['location_lng'] = $coordinates['longitude'];
        }

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
     * @OA\Post(
     *     path="/problems/{id}",
     *     summary="Update a problem",
     *     tags={"Problems"},
     *     security={{"sanctum":{}}},
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
     *     @OA\Response(response=200, description="Problem updated successfully"),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized or problem is not pending"
     *     )
     * )
     * @throws ConnectionException
     */


    public function update(ProblemUpdateRequest $request, Problem $problem): JsonResponse
    {
        if ($problem->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($problem->status !== 'pending') {
            return response()->json(['message' => 'You can only edit problems with pending status'], 403);
        }

        $address = $request->input('address');
        if ($address) {
            $coordinates = $this->gisService->getCoordinatesFromAddress($address, $problem->city_id, $problem->district_id);

            if ($coordinates) {
                $request->merge([
                    'location_lat' => $coordinates['latitude'],
                    'location_lng' => $coordinates['longitude']
                ]);
            }
        }

        $problem->update($request->all());

        if ($request->hasFile('photos')) {
            $this->deleteProblemPhotos($problem);

            foreach ($request->file('photos') as $photo) {
                if ($photo->isValid()) {
                    $userId = auth()->id();
                    $problemId = $problem->problem_id;
                    $path = Storage::disk('s3')->put("problems/User_{$userId}/problem_{$problemId}", $photo, 'public');

                    $photoUrl = Storage::disk('s3')->url($path);

                    ProblemPhoto::create([
                        'problem_id' => $problem->problem_id,
                        'photo_url' => $photoUrl,
                    ]);
                }
            }
        }

        return response()->json(['status' => true, 'problem' => $problem]);
    }


    /**
     * @OA\Delete(
     *     path="/problems/{id}",
     *     summary="Delete a problem",
     *     tags={"Problems"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the problem",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Problem deleted successfully"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function destroy(Problem $problem): JsonResponse
    {
        if ($problem->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->deleteProblemPhotos($problem);
        $problem->delete();

        return response()->json(['message' => 'Problem deleted successfully']);
    }

    /**
     * @OA\Post(
     *     path="/api/problems/{problem_id}/delete-photos",
     *     summary="Delete all photos associated with a problem",
     *     tags={"Problems"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="problem_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Problem not found")
     * )
     */


    public function deleteProblemPhotos(Problem $problem): void
    {
        $photos = ProblemPhoto::where('problem_id', $problem->problem_id)->get();
        foreach ($photos as $photo) {
            $filePath = ltrim(parse_url($photo->photo_url, PHP_URL_PATH), '/');
            $filePath = str_replace("diploma-bucket/", "", $filePath);
            if (Storage::disk('s3')->exists($filePath)) {
                Storage::disk('s3')->delete($filePath);
            }
            $photo->delete();
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/problems/{problem_id}/status",
     *     summary="Update the status of a problem",
     *     tags={"Problems"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="problem_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"in_progress", "declined", "done"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Status updated successfully")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid status"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Problem not found")
     * )
     */


    public function updateStatus(Request $request, $problem_id): JsonResponse
    {
        $user = auth()->user();

        if (!in_array($user->status, ['admin', 'moderator'])) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $problem = Problem::findOrFail($problem_id);
        $status = $request->input('status');

        if (!in_array($status, ['in_progress', 'declined', 'done'])) {
            return response()->json(['message' => 'Invalid status'], 400);
        }

        $problem->status = $status;
        $problem->save();

        return response()->json(['message' => 'Status updated successfully']);
    }

}
