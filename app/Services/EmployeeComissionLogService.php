<?php

namespace App\Services;

use App\Models\EmployeeSalesCommissionLog;
use App\Models\Sales;

class EmployeeComissionLogService
{
    /**
     * Add commission for a successful sales transaction.
     *
     * Calculates the commission based on the sales subtotal and the employee's commission percentage, and records it in the commission log.
     *
     * @param Sales $source The Sales model instance for which to add commission.
     * @return void
     */
    public function addCommission(Sales $source): void
    {
        $point = $source->sub_total * $source->employee->role->commission_percentage;

        $source->commission_flow()->save(
            EmployeeSalesCommissionLog::create([
                'description' => "Earned commission from sales {$source->code}",
                'employee_id' => $source->managed_by,
                'value' => $point,
                'type' => 1,
            ])
        );
    }

    /**
     * Subtract commission for cancelled sales or monthly deduction.
     *
     * Records a deduction of commission in the commission log. The description depends on whether the source is a sales transaction or a general deduction.
     *
     * @param mixed $source The source of the commission deduction. Can be a Sales model instance or another type.
     * @param float $value The amount to deduct from the commission.
     * @return void
     */
    public function subCommission($source, float $value): void
    {
        $description = $source instanceof Sales
            ? "Cancelled commission for refunded sales {$source->code}"
            : "Deducted commission due to monthly calculation";

        $source->commission_flow()->save(
            EmployeeSalesCommissionLog::create([
                'description' => $description,
                'employee_id' => $source->employee_id,
                'value' => $value,
                'type' => 2,
            ])
        );
    }
}
