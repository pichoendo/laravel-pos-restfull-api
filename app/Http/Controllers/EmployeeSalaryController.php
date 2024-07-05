<?php

namespace App\Http\Controllers;

use App\Models\EmployeeSalary;
use App\Http\Resources\EmployeeSalaryResource;
use App\Http\Responses\APIResponse;
use App\Services\EmployeeSalaryService;
use Exception;
use Illuminate\Http\Client\Request;

class EmployeeSalaryController extends Controller
{

    private EmployeeSalaryService $employeeSalaryService;

    /**
     * EmployeeController constructor.
     *
     * @param EmployeeSalaryService $employeeSalaryService
     */
    public function __construct(EmployeeSalaryService $employeeSalaryService)
    {
        $this->employeeSalaryService = $employeeSalaryService;
    }
    /**
     * Handle the incoming request to list employee salaries.
     *
     * @param Request $request The incoming request object, which includes query parameters.
     * @return APIResponse Returns an APIResponse object containing the requested data or an error message.
     *
     * @OA\Get(
     *      path="/employees/salaries",
     *      operationId="getEmployeeSalaries",
     *      tags={"Employee Salaries"},
     *      summary="Get employee salaries data",
     *      description="Returns list of employee salaries",
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="Number of items per page",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default=10
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="Page number",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default=0
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/EmployeeSalaryResource")
     *      ),
     *      @OA\Response(
     *          response="500",
     *          description="Failed to create category",
     *          @OA\JsonContent(ref="#/components/schemas/Error")
     *      )
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 0);
            $data = $this->employeeSalaryService->getData($request->all(), $page, $perPage);
            return APIResponse::success(EmployeeSalaryResource::collection($data), 'Fetch successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to create category. Please try again later.', 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  EmployeeSalary $employeeSalary The employee salary instance to be displayed.
     * @return \Illuminate\Http\Response Returns an API response with the employee salary data.
     * @OA\Get(
     *     path="/api/employee-salary/{employeeSalary}",
     *     summary="Fetch employee salary",
     *     description="Get a specific employee's salary details",
     *     operationId="showEmployeeSalary",
     *     tags={"EmployeeSalary"},
     *     @OA\Parameter(
     *         name="employeeSalary",
     *         in="path",
     *         required=true,
     *         description="Employee Salary ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Fetch successfully",
     *         @OA\JsonContent(ref="#/components/schemas/EmployeeSalaryResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     )
     * )
     */
    public function show(EmployeeSalary $employeeSalary)
    {
        return APIResponse::success(new EmployeeSalaryResource($employeeSalary), 'Fetch successfully', 200);
    }
}
