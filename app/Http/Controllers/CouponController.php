<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Http\Requests\StoreCouponRequest;
use App\Http\Requests\UpdateCouponRequest;
use App\Http\Resources\CouponResource;
use App\Responses\ApiResponse;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CouponController extends PaginateController implements HasMiddleware
{
    /**
     * Create a new instance of the controller.
     * 
     * 
     * This constructor initializes the `CouponController` by injecting 
     * the `CouponService` and `ApiResponse` dependencies. The `CouponService` 
     * is used for handling Coupon CRUD logic, and the `ApiResponse` 
     * is passed to the parent controller to handle standardized API responses.
     *
     * 
     * @param \App\Services\CouponService $couponService The service for managing coupons.
     * @param  \App\Http\Responses\ApiResponse  $apiResponse  The service for standardized API responses.
     */
    public function __construct(public CouponService $couponService, ApiResponse $apiResponse)
    {
        parent::__construct($apiResponse);
    }

    /**
     * Define the middleware applied to the controller methods.
     *
     * This method returns an array of middleware configurations:
     * - The 'manage_coupon' permission is required to access the 'update', 'store', and 'destroy' methods.
     * - The 'manage_coupon' or 'consume_coupon' permissions are required to access the 'index', 'show', and 'getCouponListOfItems' methods.
     *
     * @return array The array of middleware applied to the controller methods.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:manage_coupon', only: ['update',  'store', 'destroy']), // apply for manage_coupon
            new Middleware('permission:manage_coupon|consume_coupon', only: ['index', 'show', 'getCouponListOfItems',]), // apply for manage_coupon & consume_coupon
        ];
    }

    /**
     * 
     * Retrieve a paginated list of coupons.
     *
     * 
     * @OA\Get(
     *     path="/api/v1/coupon",
     *     summary="Retrieve a list of coupons",
     *     description="This endpoint is used to retrieve a paginated list of coupons. You can set the number of items per page using the 'perPage' parameter and specify the page using the 'page' parameter. It is also possible to filter the results by keyword using the 'search' parameter",
     *     operationId="GetCouponList",
     *     tags={"Coupon"},
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
     *                         @OA\Property(property="name", type="string", example="Coupon 1"),
     *                         @OA\Property(property="code", type="string", example="PROMP32"),
     *                         @OA\Property(property="value", type="int", example="20000"),
     *                         @OA\Property(property="validUntil", type="string", example="2023-02-12")
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
     * @param \Illuminate\Http\Request $request The request object containing pagination and filter parameters.
     * @return \Illuminate\Http\Response The response containing the paginated list of coupons.
     */
    public function index(Request $request)
    {
        try {

            $perPage = $request->input('per_page', $this->PER_PAGE);
            $perPage = $request->input('per_page', $this->START_PAGE);
            $search = $request->input('search', null);

            $data = $this->couponService->getData($search, $page, $perPage);
            return $this->apiResponse->success(CouponResource::collection($data), 'Fetch successful', 200);
        } catch (\Exception $ex) {
            return $this->apiResponse->error('Failed to fetch coupons. Please try again later.', 500, [$ex->getMessage()]);
        }
    }

    /**
     * 
     * Store a newly created coupon in the database.
     *
     * 
     * @OA\Post(
     *     path="/api/v1/coupon",
     *     summary="Save a coupon",
     *     description="The endpoint for create a coupon.",
     *     operationId="SaveCoupon",
     *     tags={"Coupon"},
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
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(
     *              @OA\Property(property="name", type="string", example="Promo New Year"),
     *             @OA\Property(property="code", type="string", example="CODE2024"),
     *             @OA\Property(property="value", type="int", example="2000"),
     *             @OA\Property(property="validUntil", type="string", example="2024-02-19")
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
     *                  @OA\Property(property="id", type="string", example="1"),
     *                  @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                  @OA\Property(property="name", type="string", example="Coupon 1"),
     *                  @OA\Property(property="code", type="string", example="PROMP32"),
     *                  @OA\Property(property="value", type="int", example="20000"),
     *                  @OA\Property(property="validUntil", type="string", example="2023-02-12")
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
     * @param \App\Http\Requests\StoreCouponRequest $request The request object containing validated coupon data.
     * @return \Illuminate\Http\Response The response indicating the result of the coupon creation.
     */
    public function store(StoreCouponRequest $request)
    {
        $validated = $request->validated();
        try {
            $coupon = $this->couponService->create($validated);
            return $this->apiResponse->success(new CouponResource($coupon), 'Coupon created successfully', 201);
        } catch (\Exception $ex) {
            return $this->apiResponse->error('Failed to create coupon. Please try again later.', 500, [$ex->getMessage()]);
        }
    }


    /**
     * 
     * Display the specified coupon.
     *
     * 
     * @OA\Get(
     *     path="/api/v1/coupon/{uuid}",
     *     summary="Retrieve a coupon by its UUID.",
     *     description="This endpoint allows you to retrieve details of a specific coupon using its UUID. Provide the UUID of the coupon in the request to get information such as the coupon's name, description, and any other relevant details",
     *     operationId="GetCoupon",
     *     tags={"Coupon"},
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
     *             @OA\Property(property="message", type="string", example="Data coupon fetched successfully"),
     *             @OA\Property(property="result", type="object",
     *                  @OA\Property(property="id", type="string", example="1"),
     *                  @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                  @OA\Property(property="name", type="string", example="Coupon 1"),
     *                  @OA\Property(property="code", type="string", example="PROMP32"),
     *                  @OA\Property(property="value", type="int", example="20000"),
     *                  @OA\Property(property="validUntil", type="string", example="2023-02-12")
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
     * 
     * @param \App\Models\Coupon $coupon The coupon instance to be displayed.
     * @return \Illuminate\Http\Response The response containing the coupon details.
     */
    public function show(Coupon $coupon)
    {
        return $this->apiResponse->success(new CouponResource($coupon), 'Fetch successful', 200);
    }


    /**
     * 
     * Update the specified coupon in the database.
     *
     * 
     * @OA\Put(
     *     path="/api/v1/coupon/{uuid}",
     *     summary="Update a coupon",
     *     description="This endpoint allows you to update the details of an existing coupon. Provide the coupon's UUID and the updated information in the request body to modify attributes.",
     *     operationId="UpdateCoupon",
     *     tags={"Coupon"},
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
     *             @OA\Property(property="name", type="string", example="Promo New Year"),
     *             @OA\Property(property="code", type="string", example="CODE2024"),
     *             @OA\Property(property="value", type="int", example="2000"),
     *             @OA\Property(property="validUntil", type="string", example="2024-02-19")
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
     *                  @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                  @OA\Property(property="name", type="string", example="Coupon 1"),
     *                  @OA\Property(property="code", type="string", example="PROMP32"),
     *                  @OA\Property(property="value", type="int", example="20000"),
     *                  @OA\Property(property="validUntil", type="string", example="2023-02-12")
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
     * 
     * @param \App\Http\Requests\UpdateCouponRequest $request The request object containing validated coupon data.
     * @param \App\Models\Coupon $coupon The coupon instance to be updated.
     * @return \Illuminate\Http\Response The response indicating the result of the coupon update.
     */
    public function update(UpdateCouponRequest $request, Coupon $coupon)
    {
        $validated = $request->validated();

        try {
            $updatedCoupon = $this->couponService->update($coupon, $validated);
            return $this->apiResponse->success(new CouponResource($updatedCoupon), 'Coupon updated successfully', 201);
        } catch (\Exception $ex) {
            return $this->apiResponse->error('Failed to update coupon. Please try again later.', 500, [$ex->getMessage()]);
        }
    }

    /**
     * 
     * Delete the specified coupon.
     *
     * @OA\Delete(
     *     path="/api/v1/coupon/{uuid}",
     *     summary="Delete a coupon",
     *     description="This endpoint allows you to delete a specific coupon from the system. Provide the UUID of the coupon you wish to remove, and the coupon will be permanently deleted from the database",
     *     operationId="DeleteCoupon",
     *     tags={"Coupon"},
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
     *             @OA\Property(property="message", type="string", example="Data coupon deleted successfully"),
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
     * @param \App\Models\Coupon $coupon The coupon instance to be deleted.
     * @return \Illuminate\Http\JsonResponse The JSON response indicating the result of the deletion.
     */
    public function destroy(Coupon $coupon)
    {
        try {
            $this->couponService->destroy($coupon);
            return $this->apiResponse->success(null, 'Coupon deleted successfully', 201);
        } catch (\Exception $ex) {
            return $this->apiResponse->error('Failed to delete coupon. Please try again later.', 500, [$ex->getMessage()]);
        }
    }

    /**
     * 
     * Get the list of coupon usage on sales.
     *
     * @OA\Get(
     *     path="/api/v1/coupon/{uuid}/usage",
     *     summary="Retrieve list usage of a coupon",
     *     description="This endpoint retrieves a list of how a specific coupon has been used. Provide the coupon's UUID to get details on each instance where the coupon was applied, including relevant usage information and timestamps.",
     *     operationId="GetCouponUsage",
     *     tags={"Coupon"},
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
     *         description="Response when the data retrived successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Coupon items data fetched successfully."),
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
     * @param \Illuminate\Http\Request $request The request object containing input data.
     * @param \App\Models\Coupon $coupon The coupon instance for which the list of usage is requested.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the list of coupon usage or an error message.
     */
    public function getCouponUsage(Request $request, Coupon $coupon)
    {

        $perPage = $request->input('per_page', $this->PER_PAGE);
        $perPage = $request->input('per_page', $this->START_PAGE); // Pagination usually starts from 1
        $dateRange = $request->input('date', 1);

        try {
            $data = $this->couponService->getListOfUsage($coupon, $page, $perPage, $dateRange);
            return $this->apiResponse->success(CouponResource::collection($data), 'Data fetch successfully', 200);
        } catch (\Exception $ex) {
            return $this->apiResponse->error('Failed to fetch coupon usage data.', 500, [$ex->getMessage()]);
        }
    }

    /**
     * 
     * Get the list of most used coupons.
     *
     * 
     *
     * @OA\Get(
     *     path="/api/v1/coupon/mostUsed",
     *     summary="Retrieve list of most used coupon",
     *     description="This endpoint retrieves a list of the most frequently used coupons. It provides details on the top-performing coupons based on usage frequency, including relevant information such as the number of times each coupon has been redeemed.",
     *     operationId="GetMostUsedCoupon",
     *     tags={"Coupon"},
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
     *         description="Response when the data retrived successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Coupon items data fetched successfully."),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                          @OA\Property(property="id", type="string", example="1"),
     *                          @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                          @OA\Property(property="name", type="string", example="Coupon 1"),
     *                          @OA\Property(property="code", type="string", example="PROMP32"),
     *                          @OA\Property(property="value", type="int", example="20000"),
     *                          @OA\Property(property="validUntil", type="string", example="2023-02-12")
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
     * @param \Illuminate\Http\Request $request The request object containing input data such as pagination and search parameters.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the list of most used coupons or an error message.
     */
    public function getMostUsedCoupons(Request $request)
    {
        $perPage = $request->input('per_page', $this->PER_PAGE);
        $page = $request->input('page', $this->START_PAGE); // Pagination usually starts from 1
        $dateRange = $request->input('date', null);

        try {
            $data = $this->couponService->getMostUsedCoupons($page, $perPage, $dateRange);
            return $this->apiResponse->success(CouponResource::collection($data), 'Data fetch successfully', 200);
        } catch (\Exception $ex) {
            return $this->apiResponse->error('Failed to fetch most used coupons. Please try again later.', 500, [$ex->getMessage()]);
        }
    }
}
