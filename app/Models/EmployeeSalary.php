<?php

namespace App\Models;

use App\Traits\Cacheable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalary extends Model
{
    use HasFactory, Cacheable;


    protected $fillable = [
        'employee_id',
        'month_period',
        'year_period',
        'basic_salary',
        'sales_commission',
        'total_salary'
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
            $model->clearCache();
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->user()->id ?? null;
            $model->clearCache();
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
