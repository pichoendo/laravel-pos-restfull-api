<?php

namespace App\Services;

use App\Models\EmployeeSalesCommissionLog;
use App\Models\Sales;

class EmployeeComissionLogService
{
    /**
     * Add commission for a successful sales transaction.
     *
     * @param Sales $source
     * @return void
     */
    public function addCommission(Sales $source): void
    {
        $point = $source->sub_total * $source->employee->role->commission_percentage;

        $source->comission_flow()->save(
            EmployeeSalesCommissionLog::create([
                'description' => "Earned commission from sales {$source->code}",
                'employee_id' => $source->employee_id,
                'value' => $point,
                'type' => 1,
            ])
        );
    }

    /**
     * Subtract commission for cancelled sales or monthly deduction.
     *
     * @param mixed $source
     * @param float $value
     * @return void
     */
    public function subCommission($source, float $value): void
    {
        $description = $source instanceof Sales
            ? "Cancelled commission for refunded sales {$source->code}"
            : "Deducted commission due to monthly calculation";

        $source->comission_flow()->save(
            EmployeeSalesCommissionLog::create([
                'description' => $description,
                'employee_id' => $source->employee_id,
                'value' => $value,
                'type' => 2,
            ])
        );
    }
}
