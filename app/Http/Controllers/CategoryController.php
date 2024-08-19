<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ItemResource;
use App\Responses\ApiResponse;
use App\Services\CategoryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CategoryController extends PaginateController implements HasMiddleware
{

    /**
     * Create a new instance of the controller.
     * 
     * 
     * This constructor initializes the `CategoryController` by injecting 
     * the `CategoryService` and `ApiResponse` dependencies. The `CategoryService` 
     * is used for handling Category CRUD logic, and the `ApiResponse` 
     * is passed to the parent controller to handle standardized API responses.
     *
     * 
     * @param  \App\Services\CategoryService  $categoryService  The service for handling category-related operations.
     * @param  \App\Http\Responses\ApiResponse  $apiResponse  The service for standardized API responses.
     */
    public function __construct(public CategoryService $categoryService, ApiResponse $apiResponse)
    {
        parent::__construct($apiResponse);
    }

    /**
     * 
     * 
     * Define the middleware applied to the controller methods.
     *
     * 
     * This method returns an array of middleware configurations:
     * - The 'manage_category' permission is required to access the 'update', 'store', and 'destroy' methods.
     * - The 'manage_category' or 'consume_category' permissions are required to access the 'index', 'show', and 'getCategoryListOfItems' methods.
     *
     * 
     * @return array The array of middleware applied to the controller methods.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:manage_category', only: ['update',  'store', 'destroy']), // apply for manage_category
            new Middleware('permission:manage_category|consume_category', only: ['index', 'show', 'getCategoryListOfItems',]), // apply for manage_category & consume_category
        ];
    }

    /**
     * 
     * @OA\Get(
     *     path="/api/v1/category",
     *     summary="Retrieve a list of categories",
     *     description="This endpoint is used to retrieve a paginated list of categories. You can set the number of items per page using the 'perPage' parameter and specify the page using the 'page' parameter. It is also possible to filter the results by keyword using the 'search' parameter",
     *     operationId="GetCategoryList",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer token for authentication",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer <your-token-here>"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response when the list of categories is successfully retrieved",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data category fetched successfully"),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", example="1"),
     *                         @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                         @OA\Property(property="name", type="string", example="Category 1"),
     *                        
     *                     ),
     *                 ),
     *                 @OA\Property(property="paginate", type="object",
     *                     @OA\Property(property="current_page", type="int", example="1"),
     *                     @OA\Property(property="per_page", type="int", example="10"),
     *                     @OA\Property(property="total", type="int", example="20")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Response when the session has expired or the user is not authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=401),
     *             @OA\Property(property="message", type="string", example="Your session has expired or you are not authenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Response when there are issues on the server",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=500),
     *             @OA\Property(property="message", type="string", example="Oops, something went wrong on our server. Please try again later.")
     *         )
     *     )
     * )
     * 
     * 
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('perPage', $this->PER_PAGE);
            $page = $request->input('page', $this->START_PAGE);
            $search = $request->input('search', "");

            $data = $this->categoryService->getData($search, $page, $perPage);
            return $this->apiResponse->success(CategoryResource::collection($data), 'Fetch successfully', 200);
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * 
     * Store a newly created category in the database.
     * 
     * @OA\Post(
     *     path="/api/v1/category",
     *     summary="Save a category",
     *     description="The endpoint for create a category.",
     *     operationId="SaveCategory",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer token for authentication",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer <your-token-here>"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Category 1"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response when the category is successfully created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data category saved successfully"),
     *             @OA\Property(property="result", type="object",
     *                     @OA\Property(property="id", type="string", example="1"),
     *                     @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                     @OA\Property(property="name", type="string", example="Category 1"),
     *                    
     *                 ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Response when the session has expired or the user is not authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=401),
     *             @OA\Property(property="message", type="string", example="Your session has expired or you are not authenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Response when there are issues on the validation process",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=422),
     *             @OA\Property(property="message", type="string", example="Form validation unsuccessful. Check the error details for futhermore"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="field", type="array",
     *                     @OA\Items(type="string", example="The field is required.")
     *                 ),
     *                 @OA\Property(property="anotherField", type="array",
     *                     @OA\Items(type="string", example="The field must be a string.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Response when there are issues on the server",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=500),
     *             @OA\Property(property="message", type="string", example="Oops, something went wrong on our server. Please try again later.")
     *         )
     *     )
     * )
     * 
     * 
     * @param \App\Http\Requests\StoreCategoryRequest $request The request object containing category data.
     * @return \Illuminate\Http\Response The response indicating the result of the category creation.
     * 
     * 
     */
    public function store(StoreCategoryRequest $request)
    {
        $param = $request->validated();
        try {
            $data = $this->categoryService->create($param);
            return $this->apiResponse->success(new CategoryResource($data), "Category created successfully by ", 201);
        } catch (\Exception $ex) {
            return $this->apiResponse->error('Failed to create category. Please try again later.', 500, [$ex->getMessage()]);
        }
    }

    /**
     * 
     * Display the specified category.
     *
     * 
     * @OA\Get(
     *     path="/api/v1/category/{uuid}",
     *     summary="Retrieve a category by its UUID.",
     *     description="This endpoint allows you to retrieve details of a specific category using its UUID. Provide the UUID of the category in the request to get information such as the category's name, description, and any other relevant details",
     *     operationId="GetCategory",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer token for authentication",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer <your-token-here>"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the data",
     *         @OA\Schema(
     *             type="string",
     *             example="Skfr-4584kndir4-456"
     *         )
     *     ),  
     *     @OA\Response(
     *         response=200,
     *         description="Response when the data retrived successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data category fetched successfully"),
     *             @OA\Property(property="result", type="object",
     *                     @OA\Property(property="id", type="string", example="1"),
     *                     @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                     @OA\Property(property="name", type="string", example="Category 1"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Response when the session has expired or the user is not authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=401),
     *             @OA\Property(property="message", type="string", example="Your session has expired or you are not authenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Response when the data was not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=404),
     *             @OA\Property(property="message", type="string", example="The data was not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Response when there are issues on the server",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=500),
     *             @OA\Property(property="message", type="string", example="Oops, something went wrong on our server. Please try again later.")
     *         )
     *     )
     * )
     * 
     * 
     * @param \App\Models\Category $category The category instance to be displayed.
     * @return \Illuminate\Http\Response The response containing the category data.
     * 
     * 
     */
    public function show(Category $category)
    {
        return $this->apiResponse->success(new CategoryResource($category), 'Fetch Successfully', 200);
    }

    /**
     * 
     * Update the specified category in the database.
     *
     * 
     * @OA\Put(
     *     path="/api/v1/category/{uuid}",
     *     summary="Update a category",
     *     description="This endpoint allows you to update the details of an existing category. Provide the category's UUID and the updated information in the request body to modify attributes such as the category's name and thumbnail.",
     *     operationId="UpdateCategory",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer token for authentication",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer <your-token-here>"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the data",
     *         @OA\Schema(
     *             type="string",
     *             example="Skfr-4584kndir4-456"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Category 1"),
     *         )
     *     ),
     *    @OA\Response(
     *         response=401,
     *         description="Response when the session has expired or the user is not authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=401),
     *             @OA\Property(property="message", type="string", example="Your session has expired or you are not authenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response when the data updated successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data category updated successfully"),
     *             @OA\Property(property="result", type="object",
     *                     @OA\Property(property="id", type="string", example="1"),
     *                     @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                     @OA\Property(property="name", type="string", example="Category 1"),
     *                    
     *                 ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Response when the data was not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=404),
     *             @OA\Property(property="message", type="string", example="The data was not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Response when there are issues on the validation process",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=422),
     *             @OA\Property(property="message", type="string", example="Form validation unsuccessful. Check the error details for futhermore"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="field", type="array",
     *                     @OA\Items(type="string", example="The field is required.")
     *                 ),
     *                 @OA\Property(property="anotherField", type="array",
     *                     @OA\Items(type="string", example="The field must be a string.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Response when there are issues on the server",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=500),
     *             @OA\Property(property="message", type="string", example="Oops, something went wrong on our server. Please try again later.")
     *         )
     *     )
     * )
     * 
     * 
     * @param \App\Http\Requests\UpdateCategoryRequest $request The request object containing updated category data.
     * @param \App\Models\Category $category The category instance to be updated.
     * @return \Illuminate\Http\Response The response indicating the result of the category update.
     * 
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $param = $request->validated();
        try {
            $data = $this->categoryService->update($category, $param);
            return $this->apiResponse->success(new CategoryResource($data), 'Category updated successfully by' . auth()->user()->name, 201);
        } catch (Exception $ex) {
            return $this->apiResponse->error('Failed to update category. Please try again later.', 500, [$ex->getMessage()]);
        }
    }


    /**
     * 
     * Remove the specified category from the database.
     *
     * 
     * @OA\Delete(
     *     path="/api/v1/category/{uuid}",
     *     summary="Delete a category",
     *     description="This endpoint allows you to delete a specific category from the system. Provide the UUID of the category you wish to remove, and the category will be permanently deleted from the database",
     *     operationId="DeleteCategory",
     *     tags={"Category"},
     *     @OA\Parameter(name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer token for authentication",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer <your-token-here>"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the data",
     *         @OA\Schema(
     *             type="string",
     *             example="Skfr-4584kndir4-456"
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Response when the data was not found.",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=404),
     *             @OA\Property(property="message", type="string", example="The data was not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response when the data deleted successfully.",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data category deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Response when there are issues on the validation process",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=422),
     *             @OA\Property(property="message", type="string", example="Form validation unsuccessful. Check the error details for futhermore"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="field", type="array",
     *                     @OA\Items(type="string", example="The field is required.")
     *                 ),
     *                 @OA\Property(property="anotherField", type="array",
     *                     @OA\Items(type="string", example="The field must be a string.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Response when there are issues on the server.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=500),
     *             @OA\Property(property="message", type="string", example="Oops, something went wrong on our server. Please try again later.")
     *         )
     *     )
     * )
     * 
     * 
     * 
     * @param \App\Models\Category $category The category instance to be deleted.
     * @return \Illuminate\Http\Response The response indicating the result of the deletion.
     */
    public function destroy(Category $category)
    {
        try {
            $this->categoryService->destroy($category);
            return $this->apiResponse->success(auth()->user(), 'Category deleted successfully', 201);
        } catch (Exception $ex) {
            return $this->apiResponse->error('Failed to delete category. Please try again later.', 500, [$ex->getMessage()]);
        }
    }


    /**
     * 
     * Retrieve a paginated list of items within a specified category with optional search and stock filters.
     *
     * 
     * @OA\Get(
     *     path="/api/v1/category/{uuid}/items",
     *     summary="Retrieve list items of a category",
     *     description="This endpoint retrieves a list of items associated with a specific category. Provide the UUID of the category to get a detailed list of all items that belong to it, including their attributes and relevant information.",
     *     operationId="GetCategoryListItems",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer token for authentication",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer <your-token-here>"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response when the data retrived successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category items data fetched successfully."),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", example="1"),
     *                         @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                         @OA\Property(property="name", type="string", example="Category 1"),
     *                         @OA\Property(property="price", type="string", example="6000")
     *                     ),
     *                 ),
     *                 @OA\Property(property="paginate", type="object",
     *                     @OA\Property(property="current_page", type="int", example="1"),
     *                     @OA\Property(property="per_page", type="int", example="10"),
     *                     @OA\Property(property="total", type="int", example="20")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Response when the session has expired or the user is not authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=401),
     *             @OA\Property(property="message", type="string", example="Your session has expired or you are not authenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Response when there are issues on the server",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=500),
     *             @OA\Property(property="message", type="string", example="Oops, something went wrong on our server. Please try again later.")
     *         )
     *     )
     * )
     * 
     * 
     * @param \Illuminate\Http\Request $request The request object containing pagination, search, and filter parameters.
     * @param \App\Models\Category $category The category instance for which to retrieve items.
     * @return \Illuminate\Http\Response The response containing the paginated list of items.
     */
    public function getCategoryListOfItems(Request $request, Category $category)
    {
        try {

            $perPage = $request->input('perPage', $this->PER_PAGE);
            $page = $request->input('page', $this->START_PAGE);
            $search = $request->input('search', '');
            $isLowStock = (bool) $request->input('isLowStock', false);


            $data = $this->categoryService->getListOfItems($category, $search, $page, $perPage, $isLowStock);
            return $this->apiResponse->success(ItemResource::collection($data), 'Data Category Items fetch successful', 200);
        } catch (Exception $ex) {
            return $this->apiResponse->error('Failed to fetch data. Please try again later.', 500, [$ex->getMessage()]);
        }
    }
}
