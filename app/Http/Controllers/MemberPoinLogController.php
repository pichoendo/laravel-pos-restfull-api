<?php

namespace App\Http\Controllers;

use App\Http\Resources\MemberPointLogResource;
use App\Http\Resources\MembersalesPointLogResource;
use App\Http\Responses\APIResponse;
use App\Models\MemberSalesPointLog;
use App\Services\MemberSalesPointLogService;
use Exception;
use Illuminate\Http\Request;

class MemberPoinLogController extends Controller
{
    private MemberSalesPointLogService $memberSalesPointService;

    public function __construct(MemberSalesPointLog $memberSalesPointService)
    {
        $this->memberSalesPointService = $memberSalesPointService;
    }

    /**
     * @OA\Get(
     *     path="/api/members/po",
     *     summary="List members",
     *     description="Get a paginated list of members",
     *     operationId="getMembers",
     *     tags={"Members"},
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
     *         description="Members fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Member")),
     *             @OA\Property(property="message", type="string", example="Members fetched successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to fetch members. Please try again later.")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 0);
            $data = $this->memberSalesPointService->getData($request->all(), $page, $perPage);
            return APIResponse::success(MemberPointLogResource::collection($data), 'Fetch successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to fetch data. Please try again later.', 500);
        }
    }
}
