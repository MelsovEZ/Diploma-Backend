<?php
namespace App\Http\Controllers\Problem;

use App\Http\Controllers\Controller;
use App\Http\Resources\Meta\CityResource;
use App\Http\Resources\User\UserResource;
use App\Models\Category\Category;
use App\Models\City\City;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\Meta\CategoryResource;

class ProblemResourceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/problem-resources",
     *     summary="Get categories, cities, and moderators for problem creation",
     *     description="Fetches the list of categories, cities with districts, and available moderators for the creation of a problem",
     *     operationId="getProblemResources",
     *     tags={"Problems"},
     *     @OA\Response(
     *         response=200,
     *         description="List of categories, cities with districts, and moderators",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="categories",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", nullable=true, example=1),
     *                     @OA\Property(property="name", type="string", example="Мусор")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="cities",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Алматы"),
     *                     @OA\Property(
     *                         property="districts",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="Алатауский")
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="moderators",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="name", type="string", example="Moderator"),
     *                     @OA\Property(property="surname", type="string", nullable=true, example="Da"),
     *                     @OA\Property(property="email", type="string", example="a@mail.ru"),
     *                     @OA\Property(property="photo_url", type="string", nullable=true, example="https://example.com/photo.jpg"),
     *                     @OA\Property(property="status", type="string", example="moderator")
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
        $categories = Category::all()->sortBy('id');
        $categories->prepend((object)[
            'id' => null,
            'name' => 'Все',
        ]);
        return response()->json([
            'categories' => CategoryResource::collection($categories),
            'cities' => CityResource::collection(City::with('districts')->orderBy('name')->get()),
            'moderators' => UserResource::collection(User::where('status', 'moderator')->get())
        ]);
    }
}
