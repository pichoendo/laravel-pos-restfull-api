<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     title="EmployeeSalary",
 *     description="Employee Salary model",
 *     @OA\Xml(
 *         name="EmployeeSalary"
 *     )
 * )
 */
class EmployeeSalary extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     * 
     * @OA\Property(property="employee_id", type="integer", example=1, description="ID of the employee"),
     * @OA\Property(property="month_period", type="integer", example=7, description="Month of the salary period"),
     * @OA\Property(property="year_period", type="integer", example=2024, description="Year of the salary period"),
     * @OA\Property(property="basic_salary", type="number", format="float", example=50000.00, description="Basic salary of the employee"),
     * @OA\Property(property="sales_commission", type="number", format="float", example=5000.00, description="Sales commission of the employee"),
     * @OA\Property(property="total_salary", type="number", format="float", example=55000.00, description="Total salary of the employee")
     */
    protected $fillable = [
        'employee_id', 'month_period', 'year_period', 'basic_salary', 'sales_commission', 'total_salary'
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /**
     * This method defines routines to be executed when the model is created or updated.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            $date = now()->subMonths(1);
            $model->month_period = $date->month;
            $model->year_period = $date->year;
            $model->created_by = auth()->user()->id ?? null;
            $model->updated_by = auth()->user()->id ?? null;
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->user()->id ?? null;
        });
    }

    /**
     * Get the employee that owns the salary.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the commission flow
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function commission_flow()
    {
        return $this->morphOne(EmployeeSalesCommissionLog::class, 'source');
    }
}
