<?php

namespace App\Services;

use App\Models\MemberSalesPointLog;
use App\Models\Sales;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MemberSalesPointLogService
{
    /**
     * Add points to member for a successful sales transaction.
     *
     * @param Sales $sales
     * @return void
     */
    public function getData($dateRange, $perPage, $page): Collection
    {
        $key = "MemberSalesPointLog:get:dateRanage[$dateRange]:page[$page]:perPage[$perPage]";
        $data = Cache::remember($key, now()->addHours(1), function () use ($dateRange, $perPage, $key) {
            $query =  MemberSalesPointLog::query();
            $listKey = "cache_key_list_member_sales_point";
            $list = Cache::get($listKey, []);
            $list[] = $key;
            Cache::put($listKey, $list, 600);
            if (isset($dateRange)) {
                if (isset($dateRange) && count($dateRange) === 2)
                    $query = $query->whereBetween('created_at', $dateRange);
            }
            return $query->paginate($perPage);
        });
        return $data;
    }
    /**
     * Add points to member for a successful sales transaction.
     *
     * @param Sales $sales
     * @return void
     */
    public function addPoint(Sales $sales): void
    {
        $point = $sales->sub_total * 0.01;

        DB::transaction(function () use ($sales, $point) {
            MemberSalesPointLog::create([
                'description' => "Earned points from sales {$sales->code}",
                'member_id' => $sales->member_id,
                'point' => $point,
                'sales_id' => $sales->id,
                'type' => 1,
            ]);

            $sales->member->increment('point', $point);
        });
    }

    /**
     * Subtract points from member when using points to pay for a sales transaction.
     *
     * @param Sales $sales
     * @param float $point
     * @return void
     */
    public function subPoint(Sales $sales, float $point): void
    {
        DB::transaction(function () use ($sales, $point) {
            MemberSalesPointLog::create([
                'description' => "Used points to pay for sales {$sales->code}",
                'member_id' => $sales->member_id,
                'point' => $point,
                'sales_id' => $sales->id,
                'type' => 2,
            ]);

            $sales->member->decrement('point', $point);
        });
    }
}
