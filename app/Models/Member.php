<?php

namespace App\Models;

use App\Traits\Cacheable;
use App\Services\CodeGeneratorService;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Member extends Model
{
    use HasFactory, SoftDeletes, Notifiable, Cacheable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone_no',
        'email',
        'code',
        'point',
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
            $model->code  = app(CodeGeneratorService::class)->generateCode("MEM", Member::class);
            $model->created_by = auth()->user()->id ?? null;
            $model->updated_by = auth()->user()->id ?? null;
            $model->clearCache();
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->user()->id ?? null;
            $model->clearCache(['cache_key_list_member_royal_list']);
        });

        static::deleted(function ($model) {
            $model->clearCache(['cache_key_list_member_royal_list']);
        });
    }

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
     * Get the sales made by this member.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sales()
    {
        return $this->hasMany(Sales::class);
    }

    /**
     * Get the sales point logs for this member.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sales_point()
    {
        return $this->hasMany(MemberSalesPointLog::class);
    }
}
