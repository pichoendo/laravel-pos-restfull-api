<?php

namespace App\Services;

use App\Models\Coupon;

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
}
