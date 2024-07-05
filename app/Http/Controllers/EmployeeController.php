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
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 0);
            $data = $this->employeeService->getData($request->all(), $page, $perPage);
            return APIResponse::success(EmployeeResource::collection($data), 'Fetch successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to create category. Please try again later.', 500);
        }
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

    /**
     * @OA\Get(
     *     path="/employee/commission/log",
     *     summary="Get employee commission log",
     *     tags={"Employee"},
     *     @OA\Parameter(
     *         name="employee_id",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data fetched successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/EmployeeResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to fetch employee"
     *     )
     * )
     */
    public function getEmployeeCommissionLog(Request $request, Employee $employee)
    {
        $param = $request->all();
        try {
            $data = $this->employeeService->getEmployeeCommissionLog($employee, $param);
            return APIResponse::success(EmployeeResource::collection($data), 'data fetch successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to fetch employee. Please try again later.', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/employee/{employee}/sales",
     *     summary="Get Employee Sales List",
     *     description="Fetches the list of sales for a specific employee.",
     *     operationId="getEmployeeSalesList",
     *     tags={"Employee"},
     *     @OA\Parameter(
     *         name="employee",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         ),
     *         description="Employee ID"
     *     ),
     *     @OA\Parameter(
     *         name="request",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="object"
     *         ),
     *         description="Request parameters"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/EmployeeResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to fetch employee. Please try again later.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     * Retrieve sales list for a specific employee.
     *
     * @param Request $request The HTTP request object containing parameters.
     * @param Employee $employee The employee object for whom sales list is fetched.
     * @return \Illuminate\Http\JsonResponse JSON response containing sales data.
     */
    public function getEmployeeSalesList(Request $request, Employee $employee)
    {
        $param = $request->all();
        try {
            $data = $this->employeeService->getSalesList($employee, $param);
            return APIResponse::success(EmployeeResource::collection($data), 'data fetch successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to fetch employee. Please try again later.', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/royal-employees",
     *     operationId="getRoyalEmployee",
     *     tags={"Employees"},
     *     summary="Get list of royal employees",
     *     description="Returns a list of employees with royal status",
     *     @OA\Parameter(
     *         name="param",
     *         in="query",
     *         description="Parameters to filter the employees",
     *         required=false,
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/EmployeeResource")
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="An unexpected error occurred"
     *     )
     * )
     * Get royal employees based on request parameters.
     *
     * @param Request $request The HTTP request object containing parameters.
     * @return \Illuminate\Http\JsonResponse Response containing list of royal employees or error message.
     */
    public function getRoyalEmployee(Request $request)
    {
        $param = $request->all();
        try {
            $data = $this->employeeService->getListOfRoyalEmployees($param);
            return APIResponse::success(EmployeeResource::collection($data), 'data created successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to fetch employee. Please try again later.', 500);
        }
    }
}
