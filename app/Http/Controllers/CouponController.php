<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Http\Requests\StoreCouponRequest;
use App\Http\Requests\UpdateCouponRequest;
use App\Http\Resources\CouponResource;
use App\Http\Responses\APIResponse;
use App\Services\CouponService;
use Exception;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Coupons",
 *     description="APIs for managing coupons"
 * )
 */
class CouponController extends Controller
{

    private CouponService $couponService;

    /**
     * CouponController constructor.
     *
     * @param CouponService $couponService
     */
    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    /**
     * @OA\Get(
     *     path="/api/coupons",
     *     summary="List coupons",
     *     description="Get a paginated list of coupons",
     *     operationId="getCoupons",
     *     tags={"Coupons"},
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
     *         description="Fetch successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Coupon")),
     *             @OA\Property(property="message", type="string", example="Fetch successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to fetch coupons. Please try again later.")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 0);
            $data = $this->couponService->getData($request->all(), $page, $perPage);
            return APIResponse::success(CouponResource::collection($data), 'Fetch successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to create category. Please try again later.', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/coupons",
     *     summary="Create a coupon",
     *     description="Store a newly created coupon in storage",
     *     operationId="storeCoupon",
     *     tags={"Coupons"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreCouponRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Coupon created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/Coupon"),
     *             @OA\Property(property="message", type="string", example="Coupon created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to create coupon. Please try again later.")
     *         )
     *     )
     * )
     */
    public function store(StoreCouponRequest $request)
    {
        $validated = $request->validated();

        try {
            $coupon = $this->couponService->create($validated);
            return APIResponse::success(new CouponResource($coupon), 'Coupon created successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to create coupon. Please try again later.', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/coupons/{coupon}",
     *     summary="Fetch a coupon",
     *     description="Display the specified coupon",
     *     operationId="showCoupon",
     *     tags={"Coupons"},
     *     @OA\Parameter(
     *         name="coupon",
     *         in="path",
     *         description="ID of the coupon",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Fetch successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/Coupon"),
     *             @OA\Property(property="message", type="string", example="Fetch successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Coupon not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Coupon not found")
     *         )
     *     )
     * )
     */
    public function show(Coupon $coupon)
    {
        return APIResponse::success(new CouponResource($coupon), 'Fetch successfully', 200);
    }

    /**
     * @OA\Put(
     *     path="/api/coupons/{coupon}",
     *     summary="Update a coupon",
     *     description="Update the specified coupon in storage",
     *     operationId="updateCoupon",
     *     tags={"Coupons"},
     *     @OA\Parameter(
     *         name="coupon",
     *         in="path",
     *         description="ID of the coupon",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateCouponRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Coupon updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/Coupon"),
     *             @OA\Property(property="message", type="string", example="Coupon updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Coupon not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Coupon not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to update coupon. Please try again later.")
     *         )
     *     )
     * )
     */
    public function update(UpdateCouponRequest $request, Coupon $coupon)
    {
        $validated = $request->validated();

        try {
            $updatedCoupon = $this->couponService->update($coupon, $validated);
            return APIResponse::success(new CouponResource($updatedCoupon), 'Coupon updated successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to update coupon. Please try again later.', 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/coupons/{coupon}",
     *     summary="Delete a coupon",
     *     description="Remove the specified coupon from storage",
     *     operationId="deleteCoupon",
     *     tags={"Coupons"},
     *     @OA\Parameter(
     *         name="coupon",
     *         in="path",
     *         description="ID of the coupon",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Coupon deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Coupon deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Coupon not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Coupon not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to delete coupon. Please try again later.")
     *         )
     *     )
     * )
     */
    public function destroy(Coupon $coupon)
    {
        try {
            $this->couponService->destroy($coupon);
            return APIResponse::success(null, 'Coupon deleted successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to delete coupon. Please try again later.', 500);
        }
    }

    /**
     * Get the list of coupon usage on sales.
     * 
     * @param Request $request The request object containing input data.
     * @param Category $coupon The Coupon for which the list of usage is requested.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the list of items or an error message.
     */
    public function getCouponUsage(Request $request, Coupon $coupon)
    {
        $param = $request->all();
        try {
            $data = $this->couponService->getListOfUsage($coupon, $param);
            return APIResponse::success(CouponResource::collection($data), 'Data fetch successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to fetch data.', 500);
        }
    }

    public function getMostUsedCoupons(Request $request)
    {
        $param = $request->all();
        try {
            $data = $this->couponService->getMostUsedCoupons($param);
            return APIResponse::success(CouponResource::collection($data), 'data created successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to fetch employee. Please try again later.', 500);
        }
    }
}
