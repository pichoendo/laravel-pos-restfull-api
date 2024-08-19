<?php

namespace App\Services;

use App\Models\EmployeeSalary;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class EmployeeSalaryService
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
        $key = "Employee:salary:page[$page]:perPage[$perPage]:search[$search]";

        $data = Cache::remember($key, now()->addHours(1), function () use ($search, $perPage, $key) {
            
            $query = EmployeeSalary::query();
            $listKey = "cache_key_list_employeesalary";
            $list = Cache::get($listKey, []);
            $list[] = $key;
            Cache::put($listKey, $list, 600);

            if (isset($param['search'])) {
                $search = $param['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                });
            }

            return $query->paginate($perPage);
        });
        return $data;
    }
}
