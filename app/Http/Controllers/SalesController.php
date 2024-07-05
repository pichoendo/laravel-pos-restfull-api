<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Http\Requests\StoreSalesRequest;
use App\Http\Requests\UpdateSalesRequest;
use App\Http\Resources\SalesResource;
use App\Http\Responses\APIResponse;
use App\Services\SalesService;
use Exception;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    private SalesService $salesService;


    public function __construct(SalesService $salesService)
    {
        $this->salesService = $salesService;
    }

    /**
     * @OA\Get(
     *     path="/api/sales",
     *     summary="List sales",
     *     description="Get a paginated list of sales",
     *     operationId="getSales",
     *     tags={"Sales"},
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
     *         description="Sales fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Sales")),
     *             @OA\Property(property="message", type="string", example="Sales fetched successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to fetch sales. Please try again later.")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 0);
            $data = $this->salesService->getData($request->all(), $page, $perPage);
            return APIResponse::success(SalesService::collection($data), 'Fetch successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to create category. Please try again later.', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/sales",
     *     summary="Create sales",
     *     description="Create a new sales record",
     *     operationId="createSales",
     *     tags={"Sales"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreSalesRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sales created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Sales"),
     *             @OA\Property(property="message", type="string", example="Sales created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to create sales. Please try again later.")
     *         )
     *     )
     * )
     */
    public function store(StoreSalesRequest $request)
    {
        $param = $request->validated();
        try {
            $data = $this->salesService->create($param);
            return APIResponse::success(new SalesResource($data), 'Sales created successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error($ex->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/sales/{id}",
     *     summary="Show sales",
     *     description="Get details of a specific sales record",
     *     operationId="showSales",
     *     tags={"Sales"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Sales ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sales fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Sales"),
     *             @OA\Property(property="message", type="string", example="Sales fetched successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to fetch sales. Please try again later.")
     *         )
     *     )
     * )
     */
    public function show(Sales $Sales)
    {
        return APIResponse::success(new SalesResource($Sales), 'Sales fetched successfully', 200);
    }

    /**
     * @OA\Put(
     *     path="/api/sales/{id}",
     *     summary="Update sales",
     *     description="Update an existing sales record",
     *     operationId="updateSales",
     *     tags={"Sales"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Sales ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateSalesRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sales updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Sales"),
     *             @OA\Property(property="message", type="string", example="Sales updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to update sales. Please try again later.")
     *         )
     *     )
     * )
     */
    public function update(UpdateSalesRequest $request, Sales $sale)
    {
        $param = $request->validated();
        try {
            $this->salesService->update($sale, $param);
            return APIResponse::success(new SalesResource($sale), 'Sales updated successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error($ex->getMessage(), 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/sales/{id}",
     *     summary="Delete sales",
     *     description="Delete a sales record",
     *     operationId="deleteSales",
     *     tags={"Sales"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Sales ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sales deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Sales deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to delete sales. Please try again later.")
     *         )
     *     )
     * )
     */
    public function destroy(Sales $sale)
    {
        try {
            $this->salesService->destroy($sale);
            return APIResponse::success(null, 'Sales deleted successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error($ex->getMessage(), 500);
        }
    }
}
