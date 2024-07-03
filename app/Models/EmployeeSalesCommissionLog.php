<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="EmployeeSalesCommissionLog",
 *     title="Employee Sales Commission Log",
 *     required={"employee_id", "description", "value", "type"}
 * )
 */
class EmployeeSalesCommissionLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     *
     * @OA\Property(
     *     property="employee_id",
     *     type="integer",
     *     description="The ID of the employee",
     *     example="1"
     * )
     * @OA\Property(
     *     property="description",
     *     type="string",
     *     description="The description of the sales commission log",
     *     example="Bonus for exceeding sales target"
     * )
     * @OA\Property(
     *     property="value",
     *     type="float",
     *     format="double",
     *     description="The value of the sales commission log",
     *     example="500.00"
     * )
     * @OA\Property(
     *     property="type",
     *     type="string",
     *     description="The type of the sales commission log",
     *     example="Bonus"
     * )
     */
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
     * @OA\Property(
     *     property="employee",
     *     type="object",
     *     ref="#/components/schemas/Employee"
     * )
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}

