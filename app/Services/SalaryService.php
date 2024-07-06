<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Notifications\SalaryReport;
use Illuminate\Support\Facades\DB;
use Exception;

class SalaryService
{
    private EmployeeComissionLogService $employeeComissionLogService;

    /**
     * SalaryService constructor.
     *
     * @param EmployeeComissionLogService $employeeComissionLogService
     */
    public function __construct(EmployeeComissionLogService $employeeComissionLogService)
    {
        $this->employeeComissionLogService = $employeeComissionLogService;
    }

    /**
     * Generate monthly salary for all employees.
     *
     * @return void
     * @throws Exception
     */
    public function generateSalary(): void
    {
        // Retrieve all employees with their roles
        $employees = Employee::with(['role'])->get();

        DB::beginTransaction();

        try {
            // Iterate through each employee
            foreach ($employees as $employee) {
                // Calculate the basic salary and sales commission
                $basicSalary = $employee->role->basic_salary;
                $salesCommission = $this->calculateMonthlyCommission($employee->id);
                $totalSalary = $basicSalary + $salesCommission;

                // Create a new salary record for the employee
                $salary = EmployeeSalary::create([
                    'employee_id' => $employee->id,
                    'basic_salary' => $basicSalary,
                    'sales_commission' => $salesCommission,
                    'total_salary' => $totalSalary,
                ]);

                // Deduct the calculated commission from the commission logs
                $this->employeeComissionLogService->subCommission($salary, $salesCommission);
                $salary->employee->notify( new SalaryReport($salary));
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("Error generating salaries: " . $e->getMessage());
        }
    }

    /**
     * Calculate the total sales commission for a given employee for the current month.
     *
     * @param int $employee_id
     * @return float
     */
    public function calculateMonthlyCommission(int $employee_id): float
    {
        // Calculate the total commission by summing the 'add' type and subtracting the 'subtract' type
        $commission = DB::table('employee_sales_commission_logs')
            ->select(DB::raw('SUM(CASE WHEN type = 1 THEN value ELSE 0 END) - SUM(CASE WHEN type = 2 THEN value ELSE 0 END) AS total'))
            ->where('employee_id', $employee_id)
            ->value('total');

        return $commission ?? 0.0;
    }
}