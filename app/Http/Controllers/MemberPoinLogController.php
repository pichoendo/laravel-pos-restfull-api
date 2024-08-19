<?php

namespace App\Http\Controllers;

use App\Http\Resources\MemberPointLogResource;
use App\Responses\ApiResponse;
use App\Services\MemberSalesService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;

class MemberPoinLogController extends PaginateController
{

    /**
     * MemberPoinLogController constructor.
     *
     * 
     * This constructor initializes the `MemberPoinLogController` by injecting 
     * the `MemberSalesService` and `ApiResponse` dependencies. The `MemberSalesService` 
     * is used for handling Member Sales CRUD logic, and the `ApiResponse` 
     * is passed to the parent controller to handle standardized API responses.
     *
     *  
     * @param ItemService $itemService
     * @param  \App\Http\Responses\ApiResponse  $apiResponse  The service for standardized API responses.
     */
    public function __construct(public MemberSalesService $memberSalesPointService, ApiResponse $apiResponse)
    {
        parent::__construct($apiResponse);
    }

    /**
     * Define the middleware applied to the controller methods.
     *
     * This method returns an array of middleware configurations:
     * - The 'manage_member' permission is required to access the 'update', 'store', and 'destroy' methods.
     * - The 'manage_member' or 'consume_member' permissions are required to access the 'index', 'show', and 'getCategoryListOfItems' methods.
     *
     * @return array The array of middleware applied to the controller methods.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:manage_member', only: ['update',  'store', 'destroy']), // apply for manage_member
            new Middleware('permission:manage_member|consume_member', only: ['index', 'show', 'getCategoryListOfItems',]), // apply for manage_member & consume_member
        ];
    }

    /**
     * Display a listing of the member point logs.
     *
     * @OA\Get(
     *     path="/api/v1/member/point",
     *     summary="Mengambil list data member points",
     *     description="This endpoint is used to retrieve a paginated list of members points. You can set the number of items per page using the 'perPage' parameter and specify the page using the 'page' parameter. It is also possible to filter the results by keyword using the 'search' parameter",
     *     operationId="GetMemberPointList",
     *     tags={"Member Point"},
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
     *         description="Response when the list of member points is successfully retrieved",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data member points fetched successfully"),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", example="1"),
     *                         @OA\Property(property="code", type="string", example="EMP/2020"),
     *                         @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                         @OA\Property(property="name", type="string", example="Member 1"),
     *                         @OA\Property(property="type", type="string", example="add"),
     *                         @OA\Property(property="value", type="float", example="20"),
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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', $this->PER_PAGE);
            $page = $request->input('page', $this->START_PAGE);
            $data = $this->memberSalesPointService->getData($request->all(), $page, $perPage);
            return $this->apiResponse->success(MemberPointLogResource::collection($data), 'Fetch successfully', 200);
        } catch (Exception $ex) {
            return $this->apiResponse->error('Failed to fetch data. Please try again later.', 500);
        }
    }
}
