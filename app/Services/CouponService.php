<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CouponService
{
    /**
     * Create a new coupon.
     *
     * @param array $param
     * @return Coupon
     */
    public function create(array $param): Coupon
    {
        return Coupon::create($param);
    }


    public function getData($param, $page, $perPage): LengthAwarePaginator
    {
        $query = Coupon::query();

        if (isset($param['search'])) {
            $search = $param['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }


    /**
     * Update an existing coupon.
     *
     * @param Coupon $model
     * @param array $param
     * @return Coupon
     */
    public function update(Coupon $model, array $param): Coupon
    {
        $model->update($param);
        return $model;
    }

    /**
     * Delete a coupon.
     *
     * @param Coupon $model
     * @return bool|null
     * @throws \Exception
     */
    public function destroy(Coupon $model): ?bool
    {
        return $model->delete();
    }

    /**
     * Retrieves the stock information for a given item.
     *
     * @param  \App\Models\Item  $item  The item for which stock information is needed.
     * @return \Illuminate\Database\Eloquent\Collection  A collection of item stock records.
     */
    public function getListOfUsage($coupon, $param): Collection
    {
        $query = $coupon->usage();
            
        return $query;
    }

    public function getMostUsedCoupons($param): Collection
    {
        $query = Coupon::whereHas('salesWithCoupon', function ($query) use ($param) {
            if (isset($param['dateRange']) && count($param['dateRange']) === 2)
                $query = $query->whereBetween('created_at', $param['dateRange']);
        })->withCount('salesWithCoupon')
            ->orderByDesc('sales_with_coupon_count');
            
        return $query;
    }
    
}
