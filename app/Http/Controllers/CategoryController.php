<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="Categories")
 */
class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/categories",
     *     summary="Get all categories",
     *     tags={"Categories"},
     *     @OA\Response(
     *         response=200,
     *         description="List of categories",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Trash"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-11T13:48:46.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", nullable=true, example=null)
     *             )
     *         )
     *     )
     * )
     */

    public function index(): JsonResponse
    {
        return response()->json(Category::all());
    }

    /**
     * @OA\Post(
     *     path="/categories",
     *     summary="Create a new category",
     *     tags={"Categories"},
     *     security={{ "sanctum": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="road")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Category created"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|unique:categories,name']);

        $category = Category::create(['name' => $request->name]);

        return response()->json($category, 201);
    }

    /**
     * @OA\Get(
     *     path="/categories/{category}",
     *     summary="Get a specific category",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Category ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category details",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=2),
     *             @OA\Property(property="name", type="string", example="road"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-13T18:44:30.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-13T18:53:15.000000Z")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */

    public function show(Category $category): JsonResponse
    {
        return response()->json($category);
    }

    /**
     * @OA\Put(
     *     path="/categories/{category}",
     *     summary="Update a category",
     *     tags={"Categories"},
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Category ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="road")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category updated successfully"),
     *             @OA\Property(property="category", type="object",
     *                 @OA\Property(property="id", type="integer", example=2),
     *                 @OA\Property(property="name", type="string", example="road"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-13T18:44:30.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-13T18:53:15.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Category cannot be edited",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="This category cannot be edited")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */

    public function update(Request $request, Category $category): JsonResponse
    {
        if ($category->isProtected()) {
            return response()->json(['error' => 'This category cannot be edited'], 403);
        }

        $request->validate(['name' => 'required|string|unique:categories,name']);

        $category->update(['name' => $request->name]);

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category
        ]);
    }


    /**
     * @OA\Delete(
     *     path="/categories/{category}",
     *     summary="Delete a category",
     *     tags={"Categories"},
     *     security={{ "sanctum": {} }},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Category ID"
     *     ),
     *     @OA\Response(response=200, description="Category deleted"),
     *     @OA\Response(response=403, description="Category cannot be deleted"),
     *     @OA\Response(response=404, description="Category not found")
     * )
     */
    public function destroy(Category $category): JsonResponse
    {
        if ($category->isProtected()) {
            return response()->json(['error' => 'This category cannot be deleted'], 403);
        }

        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }
}
