<?php

namespace App\Http\Controllers;

use App\Models\EmployeeSalary;
use App\Http\Resources\EmployeeSalaryResource;
use App\Responses\ApiResponse;
use App\Services\EmployeeSalaryService;
use Exception;
use Illuminate\Http\Client\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class EmployeeSalaryController extends PaginateController implements HasMiddleware
{


    /**
     * EmployeeSalaryController constructor.
     *
     * 
     * This constructor initializes the `EmployeeController` by injecting 
     * the `EmployeeSalaryService` and `ApiResponse` dependencies. The `EmployeeService` 
     * is used for handling Employee CRUD logic, and the `ApiResponse` 
     * is passed to the parent controller to handle standardized API responses.
     * 
     * 
     * @param EmployeeSalaryService $employeeSalaryService
     * @param  \App\Http\Responses\ApiResponse  $apiResponse  The service for standardized API responses.
     */
    public function __construct(public EmployeeSalaryService $employeeSalaryService, ApiResponse $apiResponse)
    {
        parent::__construct($apiResponse);
    }

    /**
     * Define the middleware applied to the controller methods.
     *
     * This method returns an array of middleware configurations:
     * - The 'manage_employee' permission is required to access the 'update', 'store', and 'destroy' methods.
     * - The 'manage_employee' or 'consume_employee' permissions are required to access the 'index', 'show', and 'getCategoryListOfItems' methods.
     *
     * @return array The array of middleware applied to the controller methods.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:manage_employee', only: ['update',  'store', 'destroy']), // apply for manage_employee
            new Middleware('permission:manage_employee|consume_employee', only: ['index', 'show', 'getCategoryListOfItems',]), // apply for manage_employee & consume_employee
        ];
    }

    /**
     * 
     * 
     * @OA\Get(
     *     path="/api/v1/employee/salary",
     *     summary="Retrieve a list of employee salary",
     *     description="This endpoint is used to retrieve a paginated list of coupons. You can set the number of items per page using the 'perPage' parameter and specify the page using the 'page' parameter. It is also possible to filter the results by keyword using the 'search' parameter",
     *     operationId="GetListEmployeeSalary",
     *     tags={"Employee Salary"},
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
     *         description="Response when the list of coupons is successfully retrieved",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data coupon fetched successfully"),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", example="1"),
     *                         @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                         @OA\Property(property="employee", type="object",
     *                              @OA\Property(property="code", type="string", example="EMP/2020"),
     *                              @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                              @OA\Property(property="name", type="string", example="Employee 1"),
     *                              @OA\Property(property="email", type="string", example="email@email.com"),
     *                              @OA\Property(property="username", type="float", example="emplo_20"),
     *                              @OA\Property(property="phone_no", type="float", example="08789387748"),
     *                              @OA\Property(property="address", type="float", example="lorem ipsum dolor ismet")
     *                         ),
     *                         @OA\Property(property="basic_salary", type="int", example="20000"),
     *                         @OA\Property(property="comission_sales", type="int", example="500"),
     *                         @OA\Property(property="total", type="int", example="20500"),
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
            $perPage = $request->input('per_page', $this->PER_PAGE);
            $page = $request->input('page', $this->START_PAGE);
            $search = $request->input('search', '');

            $data = $this->employeeSalaryService->getData($search, $page, $perPage);
            return $this->apiResponse->success(EmployeeSalaryResource::collection($data), 'Fetch successfully', 200);
        } catch (Exception $ex) {
            return $this->apiResponse->error('Failed to create category. Please try again later.', 500);
        }
    }
    /**
     * 
     *  @OA\Get(
     *     path="/api/v1/employee/salary/{uuid}",
     *     summary="Retrieve a employee salary by its UUID.",
     *     description="This endpoint allows you to retrieve details of a specific employee salary using its UUID. Provide the UUID of the employee in the request to get information such as the employee's name, description, and any other relevant details",
     *     operationId="GetEmployeeSalary",
     *     tags={"Employee Salary"},
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
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data employee fetched successfully"),
     *             @OA\Property(property="result", type="object",
     *                  @OA\Property(property="id", type="string", example="1"),
     *                         @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                         @OA\Property(property="employee", type="object",
     *                              @OA\Property(property="code", type="string", example="EMP/2020"),
     *                              @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                              @OA\Property(property="name", type="string", example="Employee 1"),
     *                              @OA\Property(property="email", type="string", example="email@email.com"),
     *                              @OA\Property(property="username", type="float", example="emplo_20"),
     *                              @OA\Property(property="phone_no", type="float", example="08789387748"),
     *                              @OA\Property(property="address", type="float", example="lorem ipsum dolor ismet")
     *                         ),
     *                         @OA\Property(property="basic_salary", type="int", example="20000"),
     *                         @OA\Property(property="comission_sales", type="int", example="500"),
     *                         @OA\Property(property="total", type="int", example="20500"),
     *                  )
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
     */
    public function show(EmployeeSalary $employeeSalary)
    {
        return $this->apiResponse->success(new EmployeeSalaryResource($employeeSalary), 'Fetch successfully', 200);
    }
}
