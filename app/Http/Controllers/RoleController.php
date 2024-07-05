<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Http\Responses\APIResponse;
use App\Services\RoleService;
use Exception;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    private RoleService $roleService;


    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * @OA\Get(
     *     path="/api/roles",
     *     summary="List roles",
     *     description="Get a paginated list of roles",
     *     operationId="getRoles",
     *     tags={"Roles"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Roles fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Role")),
     *             @OA\Property(property="message", type="string", example="Roles fetched successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to fetch roles. Please try again later.")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 0);
            $data = $this->roleService->getData($request->all(), $page, $perPage);
            return APIResponse::success(RoleResource::collection($data), 'Fetch successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to create category. Please try again later.', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/roles",
     *     summary="Create role",
     *     description="Create a new role",
     *     operationId="createRole",
     *     tags={"Roles"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreRoleRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Role"),
     *             @OA\Property(property="message", type="string", example="Role created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to create role. Please try again later.")
     *         )
     *     )
     * )
     */
    public function store(StoreRoleRequest $request)
    {
        $param = $request->validated();
        try {
            $data = $this->roleService->create($param);
            return APIResponse::success(new RoleResource($data), 'Role created successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error($ex->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/roles/{id}",
     *     summary="Show role",
     *     description="Get details of a specific role",
     *     operationId="showRole",
     *     tags={"Roles"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Role"),
     *             @OA\Property(property="message", type="string", example="Role fetched successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to fetch role. Please try again later.")
     *         )
     *     )
     * )
     */
    public function show(Role $role)
    {
        return APIResponse::success(new RoleResource($role), 'Role fetched successfully', 200);
    }

    /**
     * @OA\Put(
     *     path="/api/roles/{id}",
     *     summary="Update role",
     *     description="Update an existing role",
     *     operationId="updateRole",
     *     tags={"Roles"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateRoleRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Role"),
     *             @OA\Property(property="message", type="string", example="Role updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to update role. Please try again later.")
     *         )
     *     )
     * )
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $param = $request->validated();
        try {
            $data = $this->roleService->update($role, $param);
            return APIResponse::success(new RoleResource($data), 'Role updated successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error($ex->getMessage(), 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/roles/{id}",
     *     summary="Delete role",
     *     description="Delete a role",
     *     operationId="deleteRole",
     *     tags={"Roles"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Role ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to delete role. Please try again later.")
     *         )
     *     )
     * )
     */
    public function destroy(Role $role)
    {
        try {
            $this->roleService->destroy($role);
            return APIResponse::success(null, 'Role deleted successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error($ex->getMessage(), 500);
        }
    }
}
