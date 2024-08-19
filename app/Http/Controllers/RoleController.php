<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Responses\ApiResponse;
use App\Services\RoleService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RoleController extends PaginateController implements HasMiddleware
{

    /**
     * RoleController constructor.
     *
     * This constructor initializes the `RoleController` by injecting 
     * the `RoleService` and `ApiResponse` dependencies. The `RoleService` 
     * is used for handling Member CRUD logic, and the `ApiResponse` 
     * is passed to the parent controller to handle standardized API responses.
     *
     *  
     * @param RoleService $itemService
     * @param  \App\Http\Responses\ApiResponse  $apiResponse  The service for standardized API responses.
     */
    public function __construct(public RoleService $roleService, ApiResponse $apiResponse)
    {
        parent::__construct($apiResponse);
    }

    /**
     * Define the middleware applied to the controller methods.
     *
     * This method returns an array of middleware configurations:
     * - The 'manage_role' permission is required to access the 'update', 'store', and 'destroy' methods.
     * - The 'manage_role' or 'consume_role' permissions are required to access the 'index', 'show', and 'getCategoryListOfItems' methods.
     *
     * @return array The array of middleware applied to the controller methods.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:manage_role', only: ['update',  'store', 'destroy']), // apply for manage_role
            new Middleware('permission:manage_role|consume_role', only: ['index', 'show', 'getCategoryListOfItems',]), // apply for manage_role & consume_role
        ];
    }

    /**
     * Display a listing of the roles.
     *
     * 
     * @OA\Get(
     *     path="/api/v1/role",
     *     summary="Retrieve a list of roles",
     *     description="This endpoint is used to retrieve a paginated list of roles. You can set the number of items per page using the 'perPage' parameter and specify the page using the 'page' parameter. It is also possible to filter the results by keyword using the 'search' parameter",
     *     operationId="GetRoleList",
     *     tags={"Role"},
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
     *         description="Response when the list of roles is successfully retrieved",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data role fetched successfully"),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", example="1"),
     *                         @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                         @OA\Property(property="name", type="string", example="Role 1"),
     *                         @OA\Property(property="basic_salary", type="string", example="2000"),
     *                         @OA\Property(property="commission_percentage", type="float", example="0.3")
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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', $this->PER_PAGE);
            $page = $request->input('page', $this->START_PAGE);
            $keywords = $request->input('search', 0);

            $data = $this->roleService->getData($keywords, $page, $perPage);
            return $this->apiResponse->success(RoleResource::collection($data), 'Fetch successfully', 200);
        } catch (Exception $ex) {
            return $this->apiResponse->error('Failed to create category. Please try again later.', 500);
        }
    }
    /**
     * 
     * Store a newly created role.
     *
     * 
     * @OA\Post(
     *     path="/api/v1/role",
     *     summary="Save a role",
     *     description="The endpoint for create a role.",
     *     operationId="SaveRole",
     *     tags={"Role"},
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
     *         description="Response when the role is successfully created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data role saved successfully"),
     *             @OA\Property(property="result", type="object",
     *                  @OA\Property(property="id", type="string", example="1"),
     *                  @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                  @OA\Property(property="name", type="string", example="Role 1"),
     *                  @OA\Property(property="basic_salary", type="string", example="2000"),
     *                  @OA\Property(property="commission_percentage", type="float", example="0.3")
     *             ),
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
     * @param StoreMemberRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRoleRequest $request)
    {
        $param = $request->validated();
        try {
            $data = $this->roleService->create($param);
            return $this->apiResponse->success(new RoleResource($data), 'Role created successfully', 201);
        } catch (Exception $ex) {
            return $this->apiResponse->error($ex->getMessage(), 500);
        }
    }
    /**
     * 
     * Display the specified role.
     *
     * 
     * @OA\Get(
     *     path="/api/v1/role/{uuid}",
     *     summary="Retrieve a role by its UUID.",
     *     description="This endpoint allows you to retrieve details of a specific role using its UUID. Provide the UUID of the role in the request to get information such as the role's name, description, and any other relevant details",
     *     operationId="GetRole",
     *     tags={"Role"},
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
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="role 1"),
     *             @OA\Property(property="imageFile", type="string", example="file.png")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response when the data retrived successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data role fetched successfully"),
     *             @OA\Property(property="result", type="object",
     *                  @OA\Property(property="id", type="string", example="1"),
     *                  @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                  @OA\Property(property="name", type="string", example="Role 1"),
     *                  @OA\Property(property="basic_salary", type="string", example="2000"),
     *                  @OA\Property(property="commission_percentage", type="float", example="0.3")
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
     * @param Item $item
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Role $role)
    {
        return $this->apiResponse->success(new RoleResource($role), 'Role fetched successfully', 200);
    }
    /**
     * 
     * Update the specified role.
     *
     * @OA\Put(
     *     path="/api/v1/role/{uuid}",
     *     summary="Update a role",
     *     description="This endpoint allows you to update the details of an existing role. Provide the role's UUID and the updated information in the request body to modify attributes.",
     *     operationId="UpdateRole",
     *     tags={"Role"},
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
     *     @OA\RequestBody(required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="role 1")
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
     *         response=200,
     *         description="Response when the data updated successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data role updated successfully"),
     *             @OA\Property(property="result", type="object",
     *                  @OA\Property(property="id", type="string", example="1"),
     *                  @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                  @OA\Property(property="name", type="string", example="Role 1"),
     *                  @OA\Property(property="basic_salary", type="string", example="2000"),
     *                  @OA\Property(property="commission_percentage", type="float", example="0.3")
     *             ),
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
     * @param UpdateRoleRequest $request
     * @param Role $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $param = $request->validated();
        try {
            $data = $this->roleService->update($role, $param);
            return $this->apiResponse->success(new RoleResource($data), 'Role updated successfully', 201);
        } catch (Exception $ex) {
            return $this->apiResponse->error($ex->getMessage(), 500);
        }
    }
    /**
     * Remove the specified Role.
     *
     * @OA\Delete(
     *     path="/api/v1/role/{uuid}",
     *     summary="Delete a role",
     *     description="This endpoint allows you to delete a specific role from the system. Provide the UUID of the role you wish to remove, and the role will be permanently deleted from the database.",
     *     operationId="DeleteRole",
     *     tags={"Role"},
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
     *         response=200,
     *         description="Response when the data deleted successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data role deleted successfully"),
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
     * @param Role $role
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Role $role)
    {
        try {
            $this->roleService->destroy($role);
            return $this->apiResponse->success(null, 'Role deleted successfully', 201);
        } catch (Exception $ex) {
            return $this->apiResponse->error($ex->getMessage(), 500);
        }
    }
}
