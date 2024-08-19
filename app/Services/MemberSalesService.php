<?php

namespace App\Services;

use App\Models\MemberSalesPointLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class MemberSalesService
{
    public function getData($dateRange, $page, $perPage): LengthAwarePaginator
    {
        $key = "MemberSalesPoint:get:dateRange[$dateRange]:page[$page]:perPage[$perPage]";
        $data = Cache::remember("memberSalesPointLog-list-{$params['dateRange']}", now()->addHours(1), function () use ($params, $page, $perPage) {
            $query = MemberSalesPointLog::query();

            if (isset($params['search'])) {
                $search = $params['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                });
            }

            return $query->paginate($perPage);
        });
        return $data;
    }
}
