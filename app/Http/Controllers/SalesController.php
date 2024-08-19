<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Http\Requests\StoreSalesRequest;
use App\Http\Requests\UpdateSalesRequest;
use App\Http\Resources\SalesResource;
use App\Responses\ApiResponse;
use App\Services\SalesService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;

class SalesController extends PaginateController implements HasMiddleware
{

    /**
     * SalesController constructor.
     *
     * 
     * This constructor initializes the `SalesController` by injecting 
     * the `SalesService` and `ApiResponse` dependencies. The `SalesService` 
     * is used for handling Sales CRUD logic, and the `ApiResponse` 
     * is passed to the parent controller to handle standardized API responses.
     *
     *  
     * @param ItemService $itemService
     * @param  \App\Http\Responses\ApiResponse  $apiResponse  The service for standardized API responses.
     */
    public function __construct(public SalesService $salesService, ApiResponse $apiResponse)
    {
        parent::__construct($apiResponse);
    }

    /**
     * Define the middleware applied to the controller methods.
     *
     * This method returns an array of middleware configurations:
     * - The 'manage_sales' permission is required to access the 'update', 'store', and 'destroy' methods.
     * - The 'manage_sales' or 'consume_sales' permissions are required to access the 'index', 'show', and 'getCategoryListOfItems' methods.
     *
     * @return array The array of middleware applied to the controller methods.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:manage_sales', only: ['update',  'store', 'destroy']), // apply for manage_sales
            new Middleware('permission:manage_sales|consume_sales', only: ['index', 'show', 'getCategoryListOfItems',]), // apply for manage_sales & consume_sales
        ];
    }

    /**
     * Display a listing of the member point sales.
     *
     * @OA\Get(
     *     path="/api/v1/sales",
     *     summary="Retrieve a list of sales",
     *     description="This endpoint is used to retrieve a paginated list of sales. You can set the number of items per page using the 'perPage' parameter and specify the page using the 'page' parameter. It is also possible to filter the results by keyword using the 'search' parameter",
     *     operationId="GetSalesList",
     *     tags={"Sales"},
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
     *         description="Response when the list of sales is successfully retrieved",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data sales fetched successfully"),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", example="1"),
     *                         @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                         @OA\Property(property="code", type="string", example="PROMP32"),
     *                         @OA\Property(property="employee", type="object",
     *                              @OA\Property(property="id", type="string", example="1"),
     *                              @OA\Property(property="uuid", type="string", example="dfs-4584kndir4-456"),
     *                              @OA\Property(property="name", type="string", example="Employee 1"),
     *                         ),
     *                         @OA\Property(property="member", type="object",
     *                              @OA\Property(property="id", type="string", example="1"),
     *                              @OA\Property(property="uuid", type="string", example="643f-6dfs75-456"),
     *                              @OA\Property(property="name", type="string", example="Member 1"),
     *                         ),
     *                         @OA\Property(property="coupon", type="object",
     *                              @OA\Property(property="id", type="string", example="1"),
     *                              @OA\Property(property="name", type="string", example="New Year Promo"),
     *                              @OA\Property(property="value", type="string", example="5000"),
     *                         ),
     *                         @OA\Property(property="item", type="array",
     *                           @OA\Items(type="object",
     *                               @OA\Property(property="id", type="string", example="1"),
     *                               @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                               @OA\Property(property="item_id", type="int", example="1"),
     *                               @OA\Property(property="price", type="int", example=4000),
     *                               @OA\Property(property="qty", type="int", example=2500),
     *                               @OA\Property(property="sub_total", type="int", example=30),
     *                           )
     *                         ),
     *                         @OA\Property(property="tax", type="int", example="20000"),
     *                         @OA\Property(property="discount", type="int", example="20000"),
     *                         @OA\Property(property="sub_total", type="int", example="20000"),
     *                         @OA\Property(property="total", type="int", example="20000"),
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
            $search = $request->input('search', "");

            $data = $this->salesService->getData($search, $page, $perPage);
            return $this->apiResponse->success(SalesResource::collection($data), 'Fetch successfully', 200);
        } catch (Exception $ex) {
            return $this->apiResponse->error('Failed to create category. Please try again later.', 500);
        }
    }
    /**
     * Store a newly created sales.
     *
     * @OA\Post(
     *     path="/api/v1/sales",
     *     summary="Save a sales",
     *     description="The endpoint for create a sales.",
     *     operationId="SaveSales",
     *     tags={"Sales"},
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
     * 
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(required={"employee_id","email","phone_no"},
     *             @OA\Property(property="employee_id", type="int", example="1"),
     *             @OA\Property(property="member_id", type="int", example="3"),
     *             @OA\Property(property="coupon_id", type="int", example="4"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="item_id", type="int", example="1"),
     *                      @OA\Property(property="qty", type="int", example="2"),
     *                      @OA\Property(property="price", type="int", example="2000"),
     *                      @OA\Property(property="sub_total", type="int", example="4000"),
     *                  )
     *             ),

     *             @OA\Property(property="sub_title", type="int", example="4000"),
     *             @OA\Property(property="tax", type="int", example="400"),
     *             @OA\Property(property="total", type="int", example="4400"),
     *             @OA\Property(property="discount", type="int", example="0"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response when the coupon is successfully created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data coupon saved successfully"),
     *             @OA\Property(property="result", type="object",
     *                   @OA\Property(property="id", type="string", example="1"),
     *                         @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                         @OA\Property(property="code", type="string", example="PROMP32"),
     *                         @OA\Property(property="employee", type="object",
     *                              @OA\Property(property="id", type="string", example="1"),
     *                              @OA\Property(property="uuid", type="string", example="dfs-4584kndir4-456"),
     *                              @OA\Property(property="name", type="string", example="Employee 1"),
     *                         ),
     *                         @OA\Property(property="member", type="object",
     *                              @OA\Property(property="id", type="string", example="1"),
     *                              @OA\Property(property="uuid", type="string", example="643f-6dfs75-456"),
     *                              @OA\Property(property="name", type="string", example="Member 1"),
     *                         ),
     *                         @OA\Property(property="coupon", type="object",
     *                              @OA\Property(property="id", type="string", example="1"),
     *                              @OA\Property(property="name", type="string", example="New Year Promo"),
     *                              @OA\Property(property="value", type="string", example="5000"),
     *                         ),
     *                         @OA\Property(property="item", type="array",
     *                           @OA\Items(type="object",
     *                               @OA\Property(property="id", type="string", example="1"),
     *                               @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                               @OA\Property(property="item_id", type="int", example="1"),
     *                               @OA\Property(property="price", type="int", example=4000),
     *                               @OA\Property(property="qty", type="int", example=1),
     *                               @OA\Property(property="sub_total", type="int", example=4000),
     *                           )
     *                         ),
     *                         @OA\Property(property="tax", type="int", example="400"),
     *                         @OA\Property(property="discount", type="int", example="0"),
     *                         @OA\Property(property="sub_total", type="int", example="4000"),
     *                         @OA\Property(property="total", type="int", example="4400"),
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
     * @param StoreMemberRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreSalesRequest $request)
    {
        $param = $request->validated();
        try {
            $data = $this->salesService->create($param);
            return $this->apiResponse->success(new SalesResource($data), 'Sales created successfully', 201);
        } catch (Exception $ex) {
            Log::info("Store Sales error", [$ex->getMessage()]);
            return $this->apiResponse->error($ex->getMessage(), 500);
        }
    }

    /**
     * Display the specified sale.
     *
     * @OA\Get(
     *     path="/api/v1/sales/{uuid}",
     *     summary="Retrieve a sales by its UUID.",
     *     description="This endpoint allows you to retrieve details of a specific sales using its UUID. Provide the UUID of the sales in the request to get information such as the category's name, description, and any other relevant details",
     *     operationId="GetSales",
     *     tags={"Sales"},
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
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data category fetched successfully"),
     *             @OA\Property(property="result", type="object",
     *                      @OA\Property(property="id", type="string", example="1"),
     *                         @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                         @OA\Property(property="code", type="string", example="PROMP32"),
     *                         @OA\Property(property="employee", type="object",
     *                              @OA\Property(property="id", type="string", example="1"),
     *                              @OA\Property(property="uuid", type="string", example="dfs-4584kndir4-456"),
     *                              @OA\Property(property="name", type="string", example="Employee 1"),
     *                         ),
     *                         @OA\Property(property="member", type="object",
     *                              @OA\Property(property="id", type="string", example="1"),
     *                              @OA\Property(property="uuid", type="string", example="643f-6dfs75-456"),
     *                              @OA\Property(property="name", type="string", example="Member 1"),
     *                         ),
     *                         @OA\Property(property="coupon", type="object",
     *                              @OA\Property(property="id", type="string", example="1"),
     *                              @OA\Property(property="name", type="string", example="New Year Promo"),
     *                              @OA\Property(property="value", type="string", example="5000"),
     *                         ),
     *                         @OA\Property(property="item", type="array",
     *                           @OA\Items(type="object",
     *                               @OA\Property(property="id", type="string", example="1"),
     *                               @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                               @OA\Property(property="item_id", type="int", example="1"),
     *                               @OA\Property(property="price", type="int", example=4000),
     *                               @OA\Property(property="qty", type="int", example=1),
     *                               @OA\Property(property="sub_total", type="int", example=4000),
     *                           )
     *                         ),
     *                         @OA\Property(property="tax", type="int", example="400"),
     *                         @OA\Property(property="discount", type="int", example="0"),
     *                         @OA\Property(property="sub_total", type="int", example="4000"),
     *                         @OA\Property(property="total", type="int", example="4400"),
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
     * @param Sales $sales
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Sales $sale)
    {
        return $this->apiResponse->success(new SalesResource($sale), 'Sales fetched successfully ' . auth()->user(), 200);
    }

    /**
     * Update the specified sales.
     *
     *
     * @OA\Put(
     *     path="/api/v1/sales/{uuid}",
     *     summary="Update a sales",
     *     description="This endpoint allows you to update the details of an existing sales. Provide the sales's UUID and the updated information in the request body to modify attributes.",
     *     operationId="UpdateSales",
     *     tags={"Sales"},
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
     *         @OA\JsonContent(required={"employee_id","email","phone_no"},
     *             @OA\Property(property="employee_id", type="int", example="1"),
     *             @OA\Property(property="member_id", type="int", example="3"),
     *             @OA\Property(property="coupon_id", type="int", example="4"),
     *             @OA\Property(property="data", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="item_id", type="int", example="1"),
     *                      @OA\Property(property="qty", type="int", example="2"),
     *                      @OA\Property(property="price", type="int", example="2000"),
     *                      @OA\Property(property="sub_total", type="int", example="4000"),
     *                  )
     *             ),

     *             @OA\Property(property="sub_title", type="int", example="4000"),
     *             @OA\Property(property="tax", type="int", example="400"),
     *             @OA\Property(property="total", type="int", example="4400"),
     *             @OA\Property(property="discount", type="int", example="0"),
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
     *             @OA\Property(property="message", type="string", example="Data coupon updated successfully"),
     *             @OA\Property(property="result", type="object",
     *                  @OA\Property(property="id", type="string", example="1"),
     *                         @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                         @OA\Property(property="code", type="string", example="PROMP32"),
     *                         @OA\Property(property="employee", type="object",
     *                              @OA\Property(property="id", type="string", example="1"),
     *                              @OA\Property(property="uuid", type="string", example="dfs-4584kndir4-456"),
     *                              @OA\Property(property="name", type="string", example="Employee 1"),
     *                         ),
     *                         @OA\Property(property="member", type="object",
     *                              @OA\Property(property="id", type="string", example="1"),
     *                              @OA\Property(property="uuid", type="string", example="643f-6dfs75-456"),
     *                              @OA\Property(property="name", type="string", example="Member 1"),
     *                         ),
     *                         @OA\Property(property="coupon", type="object",
     *                              @OA\Property(property="id", type="string", example="1"),
     *                              @OA\Property(property="name", type="string", example="New Year Promo"),
     *                              @OA\Property(property="value", type="string", example="5000"),
     *                         ),
     *                         @OA\Property(property="item", type="array",
     *                           @OA\Items(type="object",
     *                               @OA\Property(property="id", type="string", example="1"),
     *                               @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                               @OA\Property(property="item_id", type="int", example="1"),
     *                               @OA\Property(property="price", type="int", example=4000),
     *                               @OA\Property(property="qty", type="int", example=1),
     *                               @OA\Property(property="sub_total", type="int", example=4000),
     *                           )
     *                         ),
     *                         @OA\Property(property="tax", type="int", example="400"),
     *                         @OA\Property(property="discount", type="int", example="0"),
     *                         @OA\Property(property="sub_total", type="int", example="4000"),
     *                         @OA\Property(property="total", type="int", example="4400"),
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
     * @param UpdateSalesRequest $request
     * @param Sales $sales
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateSalesRequest $request, Sales $sale)
    {
        $param = $request->validated();
        try {
            $this->salesService->update($sale, $param);
            return $this->apiResponse->success(new SalesResource(Sales::with(['items'])->where('id', $sale->id)->first()), 'Sales updated successfully', 201);
        } catch (Exception $ex) {
            return $this->apiResponse->error($ex->getMessage(), 500);
        }
    }
}
