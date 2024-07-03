<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Responses\APIResponse;
use App\Services\CategoryService;
use Exception;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private CategoryService $categoryService;

    /**
     * CategoryController constructor.
     *
     * @param CategoryService $categoryService
     */
    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * @OA\Get(
     * path="/api/category",
     * summary="List categories",
     * description="Get a paginated list of categories",
     * operationId="getCategories",
     * tags={"Categories"},
     * @OA\Parameter(
     * name="per_page",
     * in="query",
     * description="Number of items per page",
     * required=false,
     * @OA\Schema(type="integer", default=10)
     * ),
     * @OA\Parameter(
     * name="search",
     * in="query",
     * description="Search term",
     * required=false,
     * @OA\Schema(type="string")
     * ),
     * @OA\Response(
     * response=200,
     * description="Fetch successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Category")),
     * @OA\Property(property="message", type="string", example="Fetch successfully")
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Internal server error",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=false),
     * @OA\Property(property="message", type="string", example="Failed to fetch categories. Please try again later.")
     * )
     * )
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $query = Category::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        $query = $query->paginate($perPage);
        return APIResponse::success(CategoryResource::collection($query), 'Fetch successfully', 200);
    }

    /**
     * @OA\Post(
     * path="/api/category",
     * summary="Create category",
     * description="Create a new category",
     * operationId="createCategory",
     * tags={"Categories"},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(ref="#/components/schemas/StoreCategoryRequest")
     * ),
     * @OA\Response(
     * response=200,
     * description="Category created successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="data", ref="#/components/schemas/Category"),
     * @OA\Property(property="message", type="string", example="Category created successfully")
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Failed to create category",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=false),
     * @OA\Property(property="message", type="string", example="Failed to create category. Please try again later.")
     * )
     * )
     * )
     */
    public function store(StoreCategoryRequest $request)
    {
        $param = $request->validated();
        try {
            $data = $this->categoryService->create($param);
            return APIResponse::success(new CategoryResource($data), 'Category created successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to create category. Please try again later.', 500);
        }
    }

    /**
     * @OA\Get(
     * path="/api/category/{id}",
     * summary="Show category",
     * description="Get details of a specific category",
     * operationId="getCategory",
     * tags={"Categories"},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="Category ID",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Fetch successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="data", ref="#/components/schemas/Category"),
     * @OA\Property(property="message", type="string", example="Fetch successfully")
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Failed to fetch category",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=false),
     * @OA\Property(property="message", type="string", example="Failed to fetch category. Please try again later.")
     * )
     * )
     * )
     */
    public function show(Category $category)
    {
        return APIResponse::success(new CategoryResource($category), 'Fetch successfully', 200);
    }

    /**
     * @OA\Put(
     * path="/api/category/{id}",
     * summary="Update category",
     * description="Update an existing category",
     * operationId="updateCategory",
     * tags={"Categories"},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="Category ID",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(ref="#/components/schemas/UpdateCategoryRequest")
     * ),
     * @OA\Response(
     * response=200,
     * description="Category updated successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="data", ref="#/components/schemas/Category"),
     * @OA\Property(property="message", type="string", example="Category updated successfully")
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Failed to update category",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=false),
     * @OA\Property(property="message", type="string", example="Failed to update category. Please try again later.")
     * )
     * )
     * )
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $param = $request->validated();
        try {
            $data = $this->categoryService->update($category, $param);
            return APIResponse::success(new CategoryResource($data), 'Category updated successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to update category. Please try again later.', 500);
        }
    }

    /**
     * @OA\Delete(
     * path="/api/category/{id}",
     * summary="Delete category",
     * description="Delete an existing category",
     * operationId="deleteCategory",
     * tags={"Categories"},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="Category ID",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Deleted successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Deleted successfully")
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Failed to delete category",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=false),
     * @OA\Property(property="message", type="string", example="Failed to delete category. Please try again later.")
     * )
     * )
     * )
     */
    public function destroy(Category $category)
    {
        try {
            $this->categoryService->destroy($category);
            return APIResponse::success(null, 'Deleted successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to delete category. Please try again later.', 500);
        }
    }
}
