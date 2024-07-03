<?php

namespace App\Http\Controllers;

use App\Http\Resources\SalesResource;
use App\Http\Responses\APIResponse;
use App\Models\Member;
use Illuminate\Http\Request;

class MemberSalesController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/members/{member}/sales",
     *     summary="List member's sales",
     *     description="Get a paginated list of sales for a specific member",
     *     operationId="getMemberSales",
     *     tags={"Member Sales"},
     *     @OA\Parameter(
     *         name="member",
     *         in="path",
     *         description="Member ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
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
    public function index(Request $request, Member $member)
    {
        $perPage = $request->input('per_page', 10);

        // Fetch sales related to the member using Eloquent relationship
        $query = $member->sales()->paginate($perPage);

        // Return success response with paginated sales data
        return APIResponse::success(SalesResource::collection($query), 'Sales fetched successfully', 200);
    }
}
