<?php

namespace App\Models;

use App\Services\CodeGeneratorService;
use App\Traits\Cacheable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sales extends Model
{
    use HasFactory, SoftDeletes, Cacheable;


    protected $fillable = [
        'purchased_by',
        'code',
        'managed_by',
        'discount',
        'status',
        'tax',
        'sub_total',
        'total'
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
        $userId = auth()->user()->id ?? null;

        static::creating(function ($model) use ($userId) {
            $model->uuid = $model->uuid ?: (string) Str::uuid();
            $model->code = app(CodeGeneratorService::class)->generateCode("SAL", Sales::class);
            $model->managed_by = $userId;
            $keyCache = self::generateCacheKeys($model);
            $model->clearCache($keyCache);
        });

        static::updating(function ($model) use ($userId) {
            $model->managed_by = $userId;
            $model->clearCache(self::generateCacheKeys($model));
        });

        static::deleted(function ($model) {
            $keyCache = self::generateCacheKeys($model);
            $model->clearCache($keyCache);
        });
    }

    /**
     * Generate cache keys based on the model data.
     *
     * @param  \App\Models\Model  $model
     * @return array
     */
    protected static function generateCacheKeys($model)
    {
        $keyCache = [
            'cache_key_list_item_top_selling',
            'cache_key_list_employee_royal',
            'cache_key_list_member_royal_list',
            "cache_key_list_employee_{$model->employee_id}_commission_log",
            "cache_key_list_employee_{$model->employee_id}_managed_sales"
        ];

        if ($model->coupon) {
            $keyCache[] = 'cache_key_list_coupon_most_usage';
            $keyCache[] = "cache_key_list_coupon_usage_{$model->coupon_id}";
        }

        return $keyCache;
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
        return $this->belongsTo(Member::class, 'purchased_by', 'id');
    }

    /**
     * Get the employee who conducted this sale.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'managed_by', 'id');
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
}
