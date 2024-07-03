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

    /**
     * Create a new class instance.
     */
    public function __construct(SalesService $salesService)
    {
        $this->salesService = $salesService;
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

        // Start query builder for Sales model
        $query = Sales::query();

        // Apply search filter if 'search' parameter is provided
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // Paginate the query results
        $query = $query->paginate($perPage);

        // Return success response with paginated SalesResource collection
        return APIResponse::success(SalesResource::collection($query), 'Sales fetched successfully', 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSalesRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSalesRequest $request)
    {
        // Validate incoming request data
        $param = $request->validated();

        try {
            // Create a new sales record using SalesService
            $data = $this->salesService->create($param);
            // Return success response with newly created SalesResource
            return APIResponse::success(new SalesResource($data), 'Sales created successfully', 200);
        } catch (Exception $ex) {
            // Return error response if any exception occurs
            return APIResponse::error($ex->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Sales  $Sales
     * @return \Illuminate\Http\Response
     */
    public function show(Sales $Sales)
    {
        // Return success response with specific SalesResource data
        return APIResponse::success(new SalesResource($Sales), 'Sales fetched successfully', 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSalesRequest  $request
     * @param  \App\Models\Sales  $sale
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSalesRequest $request, Sales $sale)
    {
        // Validate incoming request data
        $param = $request->validated();

        try {
            // Update the sales record using SalesService
            $this->salesService->update($sale, $param);
            // Return success response with updated SalesResource data
            return APIResponse::success(new SalesResource($sale), 'Sales updated successfully', 200);
        } catch (Exception $ex) {
            // Return error response if any exception occurs
            return APIResponse::error($ex->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sales  $sale
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sales $sale)
    {
        try {
            // Delete the sales record using SalesService
            $this->salesService->destroy($sale);
            // Return success response upon successful deletion
            return APIResponse::success(null, 'Sales deleted successfully', 200);
        } catch (Exception $ex) {
            // Return error response if any exception occurs
            return APIResponse::error($ex->getMessage(), 500);
        }
    }
}
