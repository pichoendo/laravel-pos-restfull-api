<?php

namespace App\Services;

use App\Models\MemberSalesPointLog;
use App\Models\Sales;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class MemberSalesPointLogService
{
    /**
     * Add points to member for a successful sales transaction.
     *
     * @param Sales $sales
     * @return void
     */
    public function getData($params): Collection
    {
        $query =  MemberSalesPointLog::query();
        if (isset($paramsp['dateRange'])) {
            if (isset($params['dateRange']) && count($params['dateRange']) === 2)
                $query = $query->whereBetween('created_at', $params['dateRange']);
        }
        return $query->get();
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
