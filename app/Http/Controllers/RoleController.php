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
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        // Query roles from the database
        $query = Role::query();

        // Apply search filter if 'search' parameter is provided
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // Paginate the query results
        $query = $query->paginate($perPage);

        // Return success response with paginated roles data
        return APIResponse::success(RoleResource::collection($query), 'Roles fetched successfully', 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreRoleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRoleRequest $request)
    {
        // Validate incoming request data
        $param = $request->validated();

        try {
            // Create a new role using RoleService
            $data = $this->roleService->create($param);
            // Return success response with newly created role data
            return APIResponse::success(new RoleResource($data), 'Role created successfully', 200);
        } catch (Exception $ex) {
            // Return error response if any exception occurs
            return APIResponse::error($ex->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        // Return success response with specific role data
        return APIResponse::success(new RoleResource($role), 'Role fetched successfully', 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRoleRequest  $request
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        // Validate incoming request data
        $param = $request->validated();

        try {
            // Update the role using RoleService
            $data = $this->roleService->update($role, $param);
            // Return success response with updated role data
            return APIResponse::success(new RoleResource($data), 'Role updated successfully', 200);
        } catch (Exception $ex) {
            // Return error response if any exception occurs
            return APIResponse::error($ex->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        try {
            // Delete the role using RoleService
            $this->roleService->destroy($role);
            // Return success response upon successful deletion
            return APIResponse::success(null, 'Role deleted successfully', 200);
        } catch (Exception $ex) {
            // Return error response if any exception occurs
            return APIResponse::error($ex->getMessage(), 500);
        }
    }
}
