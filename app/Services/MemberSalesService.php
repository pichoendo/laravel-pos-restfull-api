<?php

namespace App\Services;

class MemberSalesService
{
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
