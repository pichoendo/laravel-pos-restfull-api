<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="SalesWithCoupon",
 *     description="Sales with coupon model",
 *     @OA\Xml(
 *         name="SalesWithCoupon"
 *     )
 * )
 */
class SalesWithCoupon extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     * @OA\Property(property="coupon_id", type="integer", example=1)
     */
    protected $fillable = [
        'coupon_id'
    ];

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
            $model->created_by = auth()->user()->id ?? null;
        });
    }

    /**
     * Get the sales associated with this coupon usage.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @OA\Property(property="sales", ref="#/components/schemas/Sales")
     */
    public function sales()
    {
        return $this->belongsTo(Sales::class);
    }
}
