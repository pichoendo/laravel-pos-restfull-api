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
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $query = Coupon::query();
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        $query = $query->paginate($perPage);

        return APIResponse::success(CouponResource::collection($query), 'Fetch successfully', 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCouponRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCouponRequest $request)
    {

        $param = $request->validate;
        try {
            $data = $this->couponService->create($param);
            return APIResponse::success(new CouponResource($data), 'Coupon created successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to create coupon. Please try again later.', 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Coupon  $Coupon
     * @return \Illuminate\Http\Response
     */
    public function show(Coupon $Coupon)
    {
        return APIResponse::success(new CouponResource($Coupon), 'Fetch successfully', 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCouponRequest  $request
     * @param  \App\Models\Coupon  $Coupon
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCouponRequest $request, Coupon $Coupon)
    {
        $param = $request->validated();
        try {
            $data = $this->couponService->update($Coupon, $param);
            return APIResponse::success(new CouponResource($data), 'Coupon updated successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to update coupon. Please try again later.', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Coupon  $Coupon
     * @return \Illuminate\Http\Response
     */
    public function destroy(Coupon $Coupon)
    {
        try {
            $this->couponService->destroy($Coupon);
            return APIResponse::success(null, 'Deleted successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to delete coupon. Please try again later.', 500);
        }
    }
}
