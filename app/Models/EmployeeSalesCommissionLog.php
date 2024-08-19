<?php

namespace App\Models;

use App\Traits\Cacheable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class EmployeeSalesCommissionLog extends Model
{
    use HasFactory, Cacheable;

    protected $fillable = [
        'employee_id',
        'description',
        'value',
        'type',
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            $model->clearCache();
            $model->created_by = auth()->user()->id ?? null;
        });
    }

    /**
     * Get the owning source model (polymorphic relation).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function source()
    {
        return $this->morphTo();
    }

    /**
     * Get the employee who owns this commission log.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     *
     * 
     * 
     * */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
