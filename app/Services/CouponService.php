<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;

class CouponService
{
    /**
     * Create a new coupon.
     *
     * @param array $param Associative array of attributes for the new coupon.
     * @return Coupon The created Coupon model instance.
     */
    public function create(array $param): Coupon
    {
        return Coupon::create($param);
    }

    /**
     * Retrieves paginated coupon data based on search parameters.
     *
     * This method caches the results to improve performance. The cache key includes
     * pagination and search parameters to ensure that cached data is specific to the request.
     *
     * @param string|null $search Optional search term to filter coupons by name.
     * @param int $page The page number for pagination.
     * @param int $perPage The number of coupons to return per page.
     * @return LengthAwarePaginator A paginator instance containing the filtered and paginated coupons.
     */
    public function getData(?string $search, int $page, int $perPage): LengthAwarePaginator
    {
        // Cache key includes search, page, and perPage parameters for caching specific results
        $key = "Coupon:page[$page]:perPage[$perPage]:search[$search]";

        // Attempt to retrieve the data from the cache or generate it if not present
        $data = Cache::remember($key, now()->addHours(1), function () use ($search, $perPage, $key) {
            $query = Coupon::query();
            $listKey = "cache_key_list_coupon";
            $list = Cache::get($listKey, []);
            $list[] = $key;
            Cache::put($listKey, $list, 600); // Cache key for the list of coupons

            // Apply search filter if provided
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                });
            }

            // Paginate the results
            return $query->paginate($perPage);
        });

        return $data;
    }

    /**
     * Update an existing coupon.
     *
     * @param Coupon $model The Coupon model instance to update.
     * @param array $param Associative array of attributes to update on the coupon.
     * @return Coupon The updated Coupon model instance.
     */
    public function update(Coupon $model, array $param): Coupon
    {
        $model->update($param);
        return $model;
    }

    /**
     * Delete a coupon.
     *
     * @param Coupon $model The Coupon model instance to delete.
     * @return bool|null True if the coupon was deleted successfully, false otherwise.
     * @throws \Exception If an error occurs during deletion.
     */
    public function destroy(Coupon $model): ?bool
    {
        return $model->delete();
    }

    /**
     * Retrieves the usage data for a given coupon.
     *
     * This method caches the results to improve performance. The cache key includes
     * pagination and date range parameters to ensure that cached data is specific to the request.
     *
     * @param Coupon $coupon The Coupon model instance for which to retrieve usage data.
     * @param int $page The page number for pagination.
     * @param int $perPage The number of records to return per page.
     * @param array|null $dateRange Optional date range to filter the usage records.
     * @return Collection A collection of usage records for the given coupon.
     */
    public function getListOfUsage(Coupon $coupon, int $page, int $perPage, ?array $dateRange): Collection
    {
        // Cache key includes coupon ID, page, and perPage parameters for caching specific results
        $key = "Coupon:usage:byId[{$coupon->id}]:page[$page]:perPage[$perPage]";

        // Attempt to retrieve the data from the cache or generate it if not present
        $data = Cache::remember($key, now()->addHours(1), function () use ($coupon, $dateRange, $perPage, $key) {
            $listKey = "cache_key_list_coupon_usage_{$coupon->id}";
            $list = Cache::get($listKey, []);
            $list[] = $key;
            Cache::put($listKey, $list, 600); // Cache key for the list of coupon usage

            // Query for coupon usage
            $query = $coupon->usage();
            if ($dateRange) {
                $query->whereBetween('created_at', $dateRange);
            }

            // Order by usage count and paginate the results
            return $query->orderByDesc('sales_with_coupon_count')->paginate($perPage);
        });

        return $data;
    }

    /**
     * Retrieves the most used coupons.
     *
     * This method caches the results to improve performance. The cache key includes
     * pagination parameters to ensure that cached data is specific to the request.
     *
     * @param int $page The page number for pagination.
     * @param int $perPage The number of coupons to return per page.
     * @param array|null $dateRange Optional date range to filter the coupon usage.
     * @return Collection A collection of the most used coupons.
     */
    public function getMostUsedCoupons(int $page, int $perPage, ?array $dateRange): Collection
    {
        // Cache key includes page and perPage parameters for caching specific results
        $key = "Coupon:mostUsed:page[$page]:perPage[$perPage]";

        // Attempt to retrieve the data from the cache or generate it if not present
        $data = Cache::remember($key, now()->addHours(1), function () use ($perPage, $dateRange, $key) {
            $listKey = "cache_key_list_coupon_most_usage";
            $list = Cache::get($listKey, []);
            $list[] = $key;
            Cache::put($listKey, $list, 600); // Cache key for the list of most used coupons

            // Query for most used coupons
            $query = Coupon::whereHas('salesWithCoupon', function ($query) use ($dateRange) {
                if ($dateRange) {
                    $query->whereBetween('created_at', $dateRange);
                }
            })->withCount('salesWithCoupon')
                ->orderByDesc('sales_with_coupon_count');

            // Paginate the results
            return $query->paginate($perPage);
        });

        return $data;
    }
}
