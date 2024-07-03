<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Property;
use GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\PathItem;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Operation;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\OpenApi;

class MemberSalesPointLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'point',
        'type',
        'description'
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
     * Get the sales associated with this point log.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sales()
    {
        return $this->hasMany(Sales::class);
    }

    /**
     * @OA\Schema(
     *   schema="MemberSalesPointLog",
     *   title="MemberSalesPointLog",
     *   description="MemberSalesPointLog model",
     *   @OA\Property(property="id", type="integer", description="ID"),
     *   @OA\Property(property="point", type="integer", description="Point"),
     *   @OA\Property(property="type", type="string", description="Type"),
     *   @OA\Property(property="description", type="string", description="Description"),
     *   @OA\Property(property="uuid", type="string", description="UUID"),
     *   @OA\Property(property="created_by", type="integer", description="Created By"),
     *   @OA\Property(property="created_at", type="string", format="date-time", description="Created At"),
     *   @OA\Property(property="updated_at", type="string", format="date-time", description="Updated At"),
     *   @OA\Property(property="deleted_at", type="string", format="date-time", description="Deleted At")
     * )
     */
}
