<?php

namespace App\Http\Controllers;

use App\Models\EmployeeSalesCommissionLog;
use App\Http\Resources\EmployeeSalesCommissionLogResource;
use App\Http\Responses\APIResponse;
use App\Services\EmployeeComissionService;
use Exception;
use Illuminate\Http\Client\Request;

class EmployeeSalesCommissionLogController extends Controller
{
    private EmployeeComissionService $employeeComissionService;

    /**
     * EmployeeController constructor.
     *
     * @param EmployeeComissionService $employeeComissionService
     */
    public function __construct(EmployeeComissionService $employeeComissionService)
    {
        $this->employeeComissionService = $employeeComissionService;
    }

    /**
     * Function to retrieve and display employee sales commission data with pagination.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @OA\Get(
     *     path="/api/employees/commissions",
     *     summary="Get Employee Sales Commission Logs",
     *     tags={"Employees"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=0)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/EmployeeSalesCommissionLogResource")
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Fetch successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to fetch data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Failed to create category. Please try again later."
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 0);
            $data = $this->employeeComissionService->getData($request->all(), $page, $perPage);
            return APIResponse::success(EmployeeSalesCommissionLogResource::collection($data), 'Fetch successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to create category. Please try again later.', 500);
        }
    }

    /**
     * Display the specified Employee Sales Commission Log.
     *
     * @param  EmployeeSalesCommissionLog  $employeeSalesCommissionLog  The EmployeeSalesCommissionLog model instance to be displayed.
     * @return APIResponse  Returns an APIResponse with the EmployeeSalesCommissionLogResource, message, and status code.
     * @OA\Get(
     *     path="/employeeSalesCommissionLog/{id}",
     *     operationId="getEmployeeSalesCommissionLog",
     *     tags={"EmployeeSalesCommissionLog"},
     *     summary="Get employee sales commission log details",
     *     description="Returns the sales commission log details for a specific employee",
     *     @OA\Parameter(
     *         name="id",
     *         description="EmployeeSalesCommissionLog id",
     *         required=true,
     *         in="path",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/EmployeeSalesCommissionLogResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource Not Found"
     *     )
     * )
     */
    public function show(EmployeeSalesCommissionLog $employeeSalesCommissionLog)
    {
        return APIResponse::success(new EmployeeSalesCommissionLogResource($employeeSalesCommissionLog), 'Fetch successfully', 200);
    }
}
