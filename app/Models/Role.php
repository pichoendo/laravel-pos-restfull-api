<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;

/**
 * @OA\Schema(
 *     title="Role",
 *     description="Role model",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="The ID of the role"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the role"
 *     ),
 *     @OA\Property(
 *         property="basic_salary",
 *         type="number",
 *         format="float",
 *         description="Basic salary for the role"
 *     ),
 *     @OA\Property(
 *         property="commission_percentage",
 *         type="number",
 *         format="float",
 *         description="Commission percentage for the role"
 *     ),
 *     @OA\Property(
 *         property="uuid",
 *         type="string",
 *         description="UUID of the role"
 *     ),
 *     @OA\Property(
 *         property="created_by",
 *         type="integer",
 *         format="int64",
 *         description="User ID who created the role"
 *     ),
 *     @OA\Property(
 *         property="updated_by",
 *         type="integer",
 *         format="int64",
 *         description="User ID who last updated the role"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Creation timestamp"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Last update timestamp"
 *     ),
 *     @OA\Property(
 *         property="deleted_at",
 *         type="string",
 *         format="date-time",
 *         description="Soft delete timestamp"
 *     )
 * )
 */
class Role extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'basic_salary',
        'commission_percentage'
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
            $model->updated_by = auth()->user()->id ?? null;
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->user()->id ?? null;
        });
    }

    /**
     * Get the sales associated with this role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sales()
    {
        return $this->hasMany(Sales::class);
    }
}
