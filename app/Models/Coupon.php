<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Coupon",
 *     title="Coupon",
 *     description="Coupon model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="uuid", type="string", format="uuid", example="e602c7b7-0e3d-4d45-b9e7-7f1f4a8845b1"),
 *     @OA\Property(property="name", type="string", example="Summer Sale"),
 *     @OA\Property(property="code", type="string", example="SUMMER20"),
 *     @OA\Property(property="value", type="decimal", example=20.5),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_by", type="integer", example=1),
 *     @OA\Property(property="updated_by", type="integer", example=1),
 * )
 */
class Coupon extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'code',
        'value',
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
            $model->created_by = auth()->id();
            $model->updated_by = auth()->id();
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->id();
        });
    }

    /**
     * Define a relationship with sales that use this coupon.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function salesWithCoupon()
    {
        return $this->hasMany(SalesWithCoupon::class);
    }
}
