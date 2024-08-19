<?php

namespace App\Services;

use App\Models\EmployeeSalesCommissionLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class EmployeeComissionService
{
    /**
     * Get paginated data from the database based on search parameter.
     *
     * @param array $param An array that may contain search parameter.
     * @param int $page The page number for pagination.
     * @param int $perPage Number of items per page for pagination.
     * @return LengthAwarePaginator Paginated data based on the search criteria.
     */
    public function getData($search, $page, $perPage): LengthAwarePaginator
    {
        // Define a unique cache key based on parameters
        $key = "Employee:commission:log:page[$page]:perPage[$perPage]:search[$search]";

        $data = Cache::remember($key, now()->addHours(1), function () use ($search, $key, $perPage) {
            $query = EmployeeSalesCommissionLog::query();

            $listKey = "cache_key_list_employeesalescommissionlog";
            $list = Cache::get($listKey, []);
            $list[] = $key;
            Cache::put($listKey, $list, 600);

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                });
            }

            return $query->paginate($perPage);
        });
        return $data;
    }
}
