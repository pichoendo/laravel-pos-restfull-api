<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Responses\ApiResponse;
use App\Services\EmployeeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class EmployeeController extends PaginateController implements HasMiddleware
{
    /**
     * Create a new instance of the controller.
     *
     * This constructor initializes the `EmployeeController` by injecting 
     * the `EmployeeService` and `ApiResponse` dependencies. The `EmployeeService` 
     * is used for handling Employee CRUD logic, and the `ApiResponse` 
     * is passed to the parent controller to handle standardized API responses.
     * 
     * 
     * @param EmployeeService $employeeService Service class to handle employee-related operations.
     * @param ApiResponse $apiResponse Class for standardized API responses.
     */
    public function __construct(public EmployeeService $employeeService, ApiResponse $apiResponse)
    {
        parent::__construct($apiResponse);
    }

    /**
     * Define the middleware applied to the controller methods.
     *
     * This method returns an array of middleware configurations:
     * - The 'manage_employee' permission is required to access all methods.
     * - The 'manage_employee' or 'consume_employee' permissions are required to access the 'index', 'show', and 'getCategoryListOfItems' methods.
     *
     * @return array The array of middleware applied to the controller methods.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:manage_employee', only: ['update', 'store', 'destroy', 'getEmployeeCommissionLog', 'getEmployeeSalesList', 'getRoyalEmployee']), // apply for manage_employee
            new Middleware('permission:manage_employee|consume_employee', only: ['index', 'show']),
        ];
    }

    /**
     * Display a listing of employees.
     *
     * @OA\Get(
     *     path="/api/v1/employee",
     *     summary="Mengambil list data employee",
     *     description="This endpoint is used to retrieve a paginated list of employees. You can set the number of items per page using the 'perPage' parameter and specify the page using the 'page' parameter. It is also possible to filter the results by keyword using the 'search' parameter",
     *     operationId="GetEmployeeList",
     *     tags={"Employee"},
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
     *         description="Response when the list of employees is successfully retrieved",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data employee fetched successfully"),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", example="1"),
     *                         @OA\Property(property="code", type="string", example="EMP/2020"),
     *                         @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                         @OA\Property(property="name", type="string", example="Employee 1"),
     *                         @OA\Property(property="email", type="string", example="email@email.com"),
     *                         @OA\Property(property="username", type="float", example="emplo_20"),
     *                         @OA\Property(property="phone_no", type="float", example="08789387748"),
     *                         @OA\Property(property="address", type="float", example="lorem ipsum dolor ismet")
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
     * @param Request $request The HTTP request containing query parameters for pagination and search.
     * @return \Illuminate\Http\JsonResponse JSON response with paginated employee data.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', $this->PER_PAGE);
            $page = $request->input('page', $this->START_PAGE); 
            $search = $request->input('search', "");

            $data = $this->employeeService->getData($search, $page, $perPage);
            return $this->apiResponse->success(EmployeeResource::collection($data), 'Fetch successful', 200);
        } catch (Exception $ex) {
            return $this->apiResponse->error('Failed to fetch employees. Please try again later.', 500, [$ex->getMessage()]);
        }
    }

    /**
     * Check if an employee with the given email exists.
     *
     *
     * 
  
     * 
     * @param Request $request The HTTP request containing the email to check.
     * @return \Illuminate\Http\JsonResponse JSON response indicating whether the employee exists.
     */
    public function doesEmployeeWithEmailExist(Request $request)
    {
        try {
            $email = $request->input('email');
            $data = $this->employeeService->doesEmployeeWithEmailExist($email);
            return $this->apiResponse->success($data, 'Data fetch successful', 200);
        } catch (Exception $e) {
            return $this->apiResponse->error('Failed to check email. Please try again later.', 500, [$e->getMessage()]);
        }
    }

    /**
     * Store a newly created employee.
     *
     * 
     * @OA\Post(
     *     path="/api/v1/employee",
     *     summary="Save a employee",
     *     description="The endpoint for create a employee.",
     *     operationId="SaveEmployee",
     *     tags={"Employee"},
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
     *         description="Response when the employee is successfully created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data employee saved successfully"),
     *             @OA\Property(property="result", type="object",
     *                  @OA\Property(property="id", type="string", example="1"),
     *                      @OA\Property(property="code", type="string", example="EMP/2020"),
     *                      @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                      @OA\Property(property="name", type="string", example="Employee 1"),
     *                      @OA\Property(property="email", type="string", example="email@email.com"),
     *                      @OA\Property(property="username", type="float", example="emplo_20"),
     *                      @OA\Property(property="phone_no", type="float", example="08789387748"),
     *                      @OA\Property(property="address", type="float", example="lorem ipsum dolor ismet")
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
     * @param StoreEmployeeRequest $request The HTTP request containing validated employee data.
     * @return \Illuminate\Http\JsonResponse JSON response with the created employee data.
     */
    public function store(StoreEmployeeRequest $request)
    {
        $param = $request->validated();
        try {
            $data = $this->employeeService->create($param);
            return $this->apiResponse->success(new EmployeeResource($data), 'Employee created successfully', 201);
        } catch (Exception $ex) {
            return $this->apiResponse->error('Failed to create employee. Please try again later.', 500, [$ex->getMessage()]);
        }
    }

    /**
     * Display the specified employee.
     *
     * 
     * @OA\Get(
     *     path="/api/v1/employee/{uuid}",
     *     summary="Retrieve a employee by its UUID.",
     *     description="This endpoint allows you to retrieve details of a specific employee using its UUID. Provide the UUID of the employee in the request to get information such as the employee's name, description, and any other relevant details",
     *     operationId="GetEmployee",
     *     tags={"Employee"},
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
     *            @OA\Property(property="code", type="string", example="EMP/2020"),
     *                   @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                   @OA\Property(property="name", type="string", example="Employee 1"),
     *                   @OA\Property(property="email", type="string", example="email@email.com"),
     *                   @OA\Property(property="username", type="float", example="emplo_20"),
     *                   @OA\Property(property="phone_no", type="float", example="08789387748"),
     *                   @OA\Property(property="address", type="float", example="lorem ipsum dolor ismet")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response when the data retrived successfully",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data employee fetched successfully"),
     *             @OA\Property(property="result", type="object",
     *                   @OA\Property(property="code", type="string", example="EMP/2020"),
     *                   @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                   @OA\Property(property="name", type="string", example="Employee 1"),
     *                   @OA\Property(property="email", type="string", example="email@email.com"),
     *                   @OA\Property(property="username", type="float", example="emplo_20"),
     *                   @OA\Property(property="phone_no", type="float", example="08789387748"),
     *                   @OA\Property(property="address", type="float", example="lorem ipsum dolor ismet")
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
     * @param Employee $employee The Employee model instance to retrieve.
     * @return \Illuminate\Http\JsonResponse JSON response with the employee data.
     */
    public function show(Employee $employee)
    {
        return $this->apiResponse->success(new EmployeeResource($employee), 'Fetch successful', 200);
    }

    /**
     * Update the specified employee.
     *
     * @OA\Put(
     *     path="/api/v1/employee/{uuid}",
     *     summary="Update a employee",
     *     description="This endpoint allows you to update the details of an existing employee. Provide the employee's UUID and the updated information in the request body to modify attributes.",
     *     operationId="UpdateEmployee",
     *     tags={"Employee"},
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
     *                @OA\Property(property="code", type="string", example="EMP/2020"),
     *                   @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                   @OA\Property(property="name", type="string", example="Employee 1"),
     *                   @OA\Property(property="email", type="string", example="email@email.com"),
     *                   @OA\Property(property="username", type="float", example="emplo_20"),
     *                   @OA\Property(property="phone_no", type="float", example="08789387748"),
     *                   @OA\Property(property="address", type="float", example="lorem ipsum dolor ismet")
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
     *             @OA\Property(property="message", type="string", example="Data employee updated successfully"),
     *             @OA\Property(property="result", type="object",
     *                   @OA\Property(property="code", type="string", example="EMP/2020"),
     *                   @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                   @OA\Property(property="name", type="string", example="Employee 1"),
     *                   @OA\Property(property="email", type="string", example="email@email.com"),
     *                   @OA\Property(property="username", type="float", example="emplo_20"),
     *                   @OA\Property(property="phone_no", type="float", example="08789387748"),
     *                   @OA\Property(property="address", type="float", example="lorem ipsum dolor ismet")
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
     * @param UpdateEmployeeRequest $request The HTTP request containing validated update data.
     * @param Employee $employee The Employee model instance to update.
     * @return \Illuminate\Http\JsonResponse JSON response with the updated employee data.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $param = $request->validated();
        try {
            $data = $this->employeeService->update($employee, $param);
            return $this->apiResponse->success(new EmployeeResource($data), 'Employee updated successfully', 201);
        } catch (Exception $ex) {
            return $this->apiResponse->error('Failed to update employee. Please try again later.', 500, [$ex->getMessage()]);
        }
    }

    /**
     * Remove the specified employee.
     *
     * 
     * @OA\Delete(
     *     path="/api/v1/employee/{uuid}",
     *     summary="Delete a employee",
     *     description="This endpoint allows you to delete a specific employee from the system. Provide the UUID of the employee you wish to remove, and the employee will be permanently deleted from the database",
     *     operationId="DeleteEmployee",
     *     tags={"Employee"},
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
     *             @OA\Property(property="message", type="string", example="Data employee deleted successfully"),
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
     * @param Employee $employee The Employee model instance to delete.
     * @return \Illuminate\Http\JsonResponse JSON response confirming the deletion.
     */
    public function destroy(Employee $employee)
    {
        try {
            $this->employeeService->destroy($employee);
            return $this->apiResponse->success(null, 'Employee deleted successfully', 201);
        } catch (Exception $ex) {
            return $this->apiResponse->error('Failed to delete employee. Please try again later.', 500, [$ex->getMessage()]);
        }
    }

    /**
     * Get the employee commission log.
     *
     * @OA\Get(
     *     path="/api/v1/employe/{uuid}/commission",
     *     summary="Retrive a list selected employee commissions",
     *     description="This endpoint is used to retrieve a paginated list of employee commissions. You can set the number of items per page using the 'perPage' parameter and specify the page using the 'page' parameter. It is also possible to filter the results by keyword using the 'search' parameter",
     *     operationId="GetEmployeeCommissionList",
     *     tags={"Employee"},
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
     *         description="Response when the list of employee commissions is successfully retrieved",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data employee fetched successfully"),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", example="1"),
     *                         @OA\Property(property="code", type="string", example="COM/2020"),
     *                         @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                         @OA\Property(property="name", type="string", example="Employee 1"),
     *                         @OA\Property(property="type", type="string", example="add"),
     *                         @OA\Property(property="value", type="float", example="200"),
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
     * @param Request $request The HTTP request containing optional parameters.
     * @param Employee $employee The Employee model instance for which to retrieve the commission log.
     * @return \Illuminate\Http\JsonResponse JSON response with the employee commission log data.
     */
    public function getEmployeeCommissionLog(Request $request, Employee $employee)
    {

        try {
            $perPage = $request->input('per_page', $this->PER_PAGE);
            $page = $request->input('page', $this->START_PAGE); 
            $dateRange = $request->input('dateRange', null);

            $data = $this->employeeService->getEmployeeCommissionLog($employee, $page, $perPage, $dateRange);
            return $this->apiResponse->success(EmployeeResource::collection($data), 'Data fetch successful', 200);
        } catch (Exception $ex) {
            return $this->apiResponse->error('Failed to fetch commission log. Please try again later.', 500, [$ex->getMessage()]);
        }
    }

    /**
     * Retrieve sales list for a specific employee.
     *
     * 
     * @OA\Get(
     *     path="/api/v1/employe/{uuid}/sale",
     *     summary="Retrive a list selected employee managed sales. ",
     *     description="This endpoint is used to retrieve a paginated list of employee managed sales. You can set the number of items per page using the 'perPage' parameter and specify the page using the 'page' parameter. It is also possible to filter the results by keyword using the 'search' parameter",
     *     operationId="GetEmployeeSalesList",
     *     tags={"Employee"},
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
     *         description="Response when the list of employee managed sales is successfully retrieved",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data employee fetched successfully"),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
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
     * @param Request $request The HTTP request containing pagination and date range parameters.
     * @param Employee $employee The Employee model instance for which to retrieve the sales list.
     * @return \Illuminate\Http\JsonResponse JSON response with the employee sales list data.
     */
    public function getEmployeeSalesList(Request $request, Employee $employee)
    {
        $perPage = $request->input('per_page', $this->PER_PAGE);
        $page = $request->input('page', $this->START_PAGE); 
        $dateRange = $request->input('date', null);

        try {
            $data = $this->employeeService->getSalesList($employee, $page, $perPage, $dateRange);
            return $this->apiResponse->success(EmployeeResource::collection($data), 'Data fetch successful', 200);
        } catch (Exception $ex) {
            return $this->apiResponse->error('Failed to fetch sales list. Please try again later.', 500, [$ex->getMessage()]);
        }
    }

    /**
     * Get royal employees based on request parameters.
     *
     *  /**
     * Get the employee commission log.
     *
     * @OA\Get(
     *     path="/api/v1/employe/loyal",
     *     summary="Retrive a list loyal employee",
     *     description="This endpoint is used to retrieve a paginated list of loyal employee. You can set the number of items per page using the 'perPage' parameter and specify the page using the 'page' parameter. It is also possible to filter the results by date range using the 'dateRange' parameter",
     *     operationId="GetLoyalEmployeeList",
     *     tags={"Employee"},
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
     *         description="Response when the list of employee commissions is successfully retrieved",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data employee fetched successfully"),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                          @OA\Property(property="code", type="string", example="EMP/2020"),
     *                          @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                          @OA\Property(property="name", type="string", example="Employee 1"),
     *                          @OA\Property(property="email", type="string", example="email@email.com"),
     *                          @OA\Property(property="username", type="float", example="emplo_20"),
     *                          @OA\Property(property="phone_no", type="float", example="08789387748"),
     *                          @OA\Property(property="address", type="float", example="lorem ipsum dolor ismet")
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
     * @param Request $request The HTTP request containing pagination and date range parameters.
     * @return \Illuminate\Http\JsonResponse JSON response with the royal employees data.
     */
    public function getRoyalEmployee(Request $request)
    {
        $perPage = $request->input('per_page', $this->PER_PAGE);
        $page = $request->input('page', $this->START_PAGE); 
        $dateRange = $request->input('date', null);

        try {
            $data = $this->employeeService->getListOfRoyalEmployees($page, $perPage, $dateRange);
            return $this->apiResponse->success(EmployeeResource::collection($data), 'Data fetch successful', 200);
        } catch (Exception $ex) {
            return $this->apiResponse->error('Failed to fetch royal employees. Please try again later.', 500, [$ex->getMessage()]);
        }
    }
}
