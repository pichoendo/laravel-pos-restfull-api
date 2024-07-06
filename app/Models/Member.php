<?php

namespace App\Models;

use App\Services\CodeGeneratorService;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

/**
 * @OA\Schema(
 *     title="Member",
 *     description="Member model",
 *     @OA\Property(
 *         property="id",
 *         description="ID of the member",
 *         type="integer",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         description="Name of the member",
 *         type="string",
 *         example="John Doe"
 *     ),
 *     @OA\Property(
 *         property="phone_no",
 *         description="Phone number of the member",
 *         type="string",
 *         example="123-456-7890"
 *     ),
 *     @OA\Property(
 *         property="code",
 *         description="Generated code for the member",
 *         type="string",
 *         example="MEM123"
 *     ),
 *     @OA\Property(
 *         property="point",
 *         description="Points of the member",
 *         type="integer",
 *         example="100"
 *     ),
 *     @OA\Property(
 *         property="uuid",
 *         description="UUID of the member",
 *         type="string",
 *         example="a1b2c3d4-e5f6-7890"
 *     ),
 *     @OA\Property(
 *         property="created_by",
 *         description="ID of the user who created the member",
 *         type="integer",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="updated_by",
 *         description="ID of the user who updated the member",
 *         type="integer",
 *         example="1"
 *     ),
 * )
 */
class Member extends Model
{
    use HasFactory, SoftDeletes,Notifiable;

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
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->user()->id ?? null;
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
