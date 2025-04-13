<?php
namespace App\Http\Controllers\Problem;

use App\Http\Controllers\Controller;
use App\Http\Resources\Meta\CityResource;
use App\Models\Category\Category;
use App\Models\City\City;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\Meta\CategoryResource;

class ProblemResourceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/problems/resources",
     *     summary="Get categories and cities for problem creation",
     *     description="Fetches the list of categories and cities for the creation of a problem",
     *     operationId="getProblemResources",
     *     tags={"Problems"},
     *     @OA\Response(
     *         response=200,
     *         description="List of categories and cities",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="categories",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Мусор")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="cities",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Алматы")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'categories' => CategoryResource::collection(Category::all()),
            'cities' => CityResource::collection(City::orderBy('name')->get()),
        ]);
    }
}
