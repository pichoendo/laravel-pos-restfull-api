<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class EmployeeService
{
    /**
     * Create a new employee.
     *
     * @param array $param
     * @return Employee
     */
    public function create(array $param): Employee
    {
        return Employee::create($param);
    }

    public function getData($param, $page, $perPage): LengthAwarePaginator
    {
        $query = Employee::query();

        if (isset($param['search'])) {
            $search = $param['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Update an existing employee.
     *
     * @param Employee $model
     * @param array $param
     * @return Employee
     */
    public function update(Employee $model, array $param): Employee
    {
        $model->update($param);
        return $model;
    }

    /**
     * Delete an employee.
     *
     * @param Employee $model
     * @return bool|null
     * @throws \Exception
     */
    public function destroy(Employee $model): ?bool
    {
        return $model->delete();
    }

    /**
     * Retrieves a list of sales associated with a given employee.
     * 
     * @param  Employee $employee The employee model instance.
     * @param  array $param An array of parameters to filter the sales.
     * @return Collection A collection of sales records.
     */
    public function getSalesList($employee, $param): Collection
    {
        $query = $employee->sales();
        if (isset($param['dateRange']) && count($param['dateRange']) === 2)
            $query = $query->whereBetween('created_at', $param['dateRange']);

        return $query->get();
    }


    public function getListOfRoyalEmployees($param): Collection
    {
        $query = Employee::whereHas('sales', function ($query) use ($param) {
            if (isset($param['dateRange']) && count($param['dateRange']) === 2)
                $query = $query->whereBetween('created_at', $param['dateRange']);
        })->withCount('sales')
            ->orderByDesc('sales_count');


        return $query->get();
    }

    public function getEmployeeCommissionLog($employee, $param): Collection
    {
        $query = $employee->commisionLog()->whereHas('sales', function ($query) use ($param) {
            if (isset($param['dateRange']) && count($param['dateRange']) === 2)
                $query = $query->whereBetween('created_at', $param['dateRange']);
        })->orderByDesc('sales_count');


        return $query->get();
    }
}
