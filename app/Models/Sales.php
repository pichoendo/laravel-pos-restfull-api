<?php

namespace App\Models;

use App\Services\CodeGeneratorService;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     title="Sales",
 *     description="Sales model",
 *     @OA\Xml(
 *         name="Sales"
 *     )
 * )
 */
class Sales extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     * @OA\Property(property="member_id", type="integer", example=1)
     * @OA\Property(property="employee_id", type="integer", example=1)
     * @OA\Property(property="discount", type="number", format="float", example=0.1)
     * @OA\Property(property="status", type="string", example="pending")
     * @OA\Property(property="tax", type="number", format="float", example=0.2)
     * @OA\Property(property="sub_total", type="number", format="float", example=100.00)
     * @OA\Property(property="total", type="number", format="float", example=120.00)
     */

    protected $fillable = [
        'member_id',
        'employee_id',
        'discount',
        'status',
        'tax',
        'sub_total',
        'total'
    ];

    /**
     * The attributes that should be appended to JSON responses.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'code',
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
            $model->code = app(CodeGeneratorService::class)->generateCode("SAL", Sales::class);
            $model->created_by = auth()->user()->id ?? null;
            $model->updated_by = auth()->user()->id ?? null;
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->user()->id ?? null;
        });
    }

    /**
     * Get the sales items associated with this sale.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(SalesItem::class);
    }

    /**
     * Get the member who made this sale.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the employee who conducted this sale.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the card payments associated with this sale.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payWithCard()
    {
        return $this->hasMany(SalesPaymentWithCard::class);
    }

    /**
     * Get the commission flow associated with this sale.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function commission_flow()
    {
        return $this->morphOne(EmployeeSalesCommissionLog::class, 'source');
    }

    /**
     * Accessor to get the generated code for the sale.
     *
     * @return string
     */
    public function getCodeAttribute()
    {
        return $this->code;
    }
}
