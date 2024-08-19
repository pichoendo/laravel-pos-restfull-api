<?php

namespace App\Http\Controllers;

use App\Models\EmployeeSalesCommissionLog;
use App\Http\Resources\EmployeeSalesCommissionLogResource;
use App\Services\EmployeeComissionService;
use Illuminate\Http\Request; // Corrected Request namespace
use Exception;
use Illuminate\Routing\Controllers\Middleware;

class EmployeeSalesCommissionLogController extends Controller
{


    /**
     * EmployeeSalesCommissionLogController constructor.
     *
     * 
     * This constructor initializes the `EmployeeSalesCommissionLogController` by injecting 
     * the `EmployeeComissionService` and `ApiResponse` dependencies. The `EmployeeComissionService` 
     * is used for handling EmployeeComission CRUD logic, and the `ApiResponse` 
     * is passed to the parent controller to handle standardized API responses.
     *
     * 
     * 
     * 
     * @param EmployeeComissionService $employeeComissionService
     * @param  \App\Http\Responses\ApiResponse  $apiResponse  The service for standardized API responses.
     */
    public function __construct(public EmployeeComissionService $employeeComissionService, ApiResponse $apiResponse)
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
     * Display a listing of the employee sales commission logs.
     *
     * 
     * @OA\Get(
     *     path="/api/v1/employee/commission",
     *     summary="Retrieve a list of employee commission",
     *     description="This endpoint is used to retrieve a paginated list of employee commission. You can set the number of items per page using the 'perPage' parameter and specify the page using the 'page' parameter. It is also possible to filter the results by keyword using the 'search' parameter",
     *     operationId="GetListEmployeeCommission",
      *    tags={"Employee Sales Commission"},
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
     *                         @OA\Property(property="type", type="string", example="add"),
     *                         @OA\Property(property="value", type="int", example="20500"),
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
     * 
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Get pagination parameters with defaults
            $perPage = $request->input('per_page', $this->PER_PAGE);
            $page = $request->input('page', $this->START_PAGE); // Changed default to 1 for typical pagination

            // Fetch data using the service
            $data = $this->employeeComissionService->getData($request->all(), $page, $perPage);

            return $this->apiResponse->success(EmployeeSalesCommissionLogResource::collection($data), 'Data fetched successfully.', 200);
        } catch (Exception $ex) {

            return $this->apiResponse->error('Failed to fetch employee sales commission logs. Please try again later.', 500);
        }
    }

    /**
     * Display the specified employee sales commission log.
     * 
     * 
     *  @OA\Get(
     *     path="/api/v1/employee/commission/{uuid}",
     *     summary="Retrieve a employee sales commission by its UUID.",
     *     description="This endpoint allows you to retrieve details of a specific employee sales commission using its UUID. Provide the UUID of the employee in the request to get information such as the employee's name, description, and any other relevant details",
     *     operationId="GetEmployeeSalesCommission",
     *     tags={"Employee Sales Commission"},
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
     *                 
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
     *                         @OA\Property(property="type", type="string", example="add"),
     *                         @OA\Property(property="value", type="int", example="20500"),
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
     * @param EmployeeSalesCommissionLog $employeeSalesCommissionLog
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(EmployeeSalesCommissionLog $employeeSalesCommissionLog)
    {
        // Return a successful response with the single resource
        return $this->apiResponse->success(new EmployeeSalesCommissionLogResource($employeeSalesCommissionLog), 'Data fetched successfully.', 200);
    }
}
