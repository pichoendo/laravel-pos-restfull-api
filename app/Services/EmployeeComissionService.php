<?php

namespace App\Services;

use App\Models\EmployeeSalesCommissionLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
    public function getData($param, $page, $perPage): LengthAwarePaginator
    {
        $query = EmployeeSalesCommissionLog::query();

        if (isset($param['search'])) {
            $search = $param['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }
}
