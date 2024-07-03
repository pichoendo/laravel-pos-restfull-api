<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Http\Responses\APIResponse;
use App\Services\EmployeeService;
use Exception;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{

    private EmployeeService $employeeService;

    /**
     * EmployeeController constructor.
     *
     * @param EmployeeService $employeeService
     */
    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    /**
     * @OA\Get(
     *     path="/api/employees",
     *     summary="Get employees",
     *     description="Fetch a list of employees with optional search query",
     *     operationId="getEmployees",
     *     tags={"Employees"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of employees per page",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Employees fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Employee")),
     *             @OA\Property(property="message", type="string", example="Fetch successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to fetch employees. Please try again later.")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $query = Employee::query();
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $query = $query->paginate($perPage);

        return APIResponse::success(EmployeeResource::collection($query), 'Fetch successfully', 200);
    }


    /**
     * @OA\Post(
     *     path="/api/employees",
     *     summary="Create employee",
     *     description="Create a new employee",
     *     operationId="storeEmployee",
     *     tags={"Employees"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreEmployeeRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Employee created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Employee"),
     *             @OA\Property(property="message", type="string", example="Employee created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to create employee. Please try again later.")
     *         )
     *     )
     * )
     */
    public function store(StoreEmployeeRequest $request)
    {
        $param = $request->validated();
        try {
            $data = $this->employeeService->create($param);
            return APIResponse::success(new EmployeeResource($data), 'Employee created successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to create employee. Please try again later.', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/employees/{employee}",
     *     summary="Get employee",
     *     description="Fetch a specific employee by ID",
     *     operationId="getEmployee",
     *     tags={"Employees"},
     *     @OA\Parameter(
     *         name="employee",
     *         in="path",
     *         description="Employee ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Employee fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Employee"),
     *             @OA\Property(property="message", type="string", example="Fetch successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to fetch employee. Please try again later.")
     *         )
     *     )
     * )
     */
    public function show(Employee $employee)
    {
        return APIResponse::success(new EmployeeResource($employee), 'Fetch successfully', 200);
    }

    /**
     * @OA\Put(
     *     path="/api/employees/{employee}",
     *     summary="Update employee",
     *     description="Update an existing employee",
     *     operationId="updateEmployee",
     *     tags={"Employees"},
     *     @OA\Parameter(
     *         name="employee",
     *         in="path",
     *         description="Employee ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateEmployeeRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Employee updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Employee"),
     *             @OA\Property(property="message", type="string", example="Employee updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to update employee. Please try again later.")
     *         )
     *     )
     * )
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $param = $request->validated();
        try {
            $data = $this->employeeService->update($employee, $param);
            return APIResponse::success(new EmployeeResource($data), 'Employee updated successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to update employee. Please try again later.', 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/employees/{employee}",
     *     summary="Delete employee",
     *     description="Delete a specific employee by ID",
     *     operationId="deleteEmployee",
     *     tags={"Employees"},
     *     @OA\Parameter(
     *         name="employee",
     *         in="path",
     *         description="Employee ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Employee deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to delete employee. Please try again later.")
     *         )
     *     )
     * )
     */
    public function destroy(Employee $employee)
    {
        try {
            $this->employeeService->destroy($employee);
            return APIResponse::success(null, 'Deleted successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to delete employee. Please try again later.', 500);
        }
    }
}
