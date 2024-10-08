<?php

namespace App\Models;

use App\Traits\Cacheable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Coupon extends Model
{
    use HasFactory, SoftDeletes ,Cacheable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
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
            $model->clearCache();
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->id();
            $model->clearCache(['cache_key_list_coupon_most_usage', "cache_key_list_coupon_usage_$model->id"]);
        });

        static::deleted(function ($model) {
            $model->updated_by = auth()->id();
            $model->clearCache(['cache_key_list_coupon_most_usage', "cache_key_list_coupon_usage_$model->id"]);
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
