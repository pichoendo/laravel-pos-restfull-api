<?php

namespace App\Services;

use App\Models\Employee;
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
}
