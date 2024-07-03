<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     title="SalesItem",
 *     description="SalesItem model",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID of the sales item"
 *     ),
 *     @OA\Property(
 *         property="qty",
 *         type="integer",
 *         description="Quantity of the item"
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="number",
 *         format="float",
 *         description="Price per unit of the item"
 *     ),
 *     @OA\Property(
 *         property="sales_id",
 *         type="integer",
 *         description="ID of the sales order"
 *     ),
 *     @OA\Property(
 *         property="item_id",
 *         type="integer",
 *         description="ID of the item"
 *     ),
 *     @OA\Property(
 *         property="sub_total",
 *         type="number",
 *         format="float",
 *         description="Subtotal of the sales item"
 *     ),
 *     @OA\Property(
 *         property="uuid",
 *         type="string",
 *         description="UUID of the sales item"
 *     ),
 *     @OA\Property(
 *         property="created_by",
 *         type="integer",
 *         description="ID of the user who created the sales item"
 *     ),
 *     @OA\Property(
 *         property="updated_by",
 *         type="integer",
 *         description="ID of the user who last updated the sales item"
 *     )
 * )
 */
class SalesItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'qty',
        'price',
        'sales_id',
        'item_id',
        'sub_total'
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
     * Get the item associated with this sales item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the sales order associated with this sales item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sales()
    {
        return $this->belongsTo(Sales::class);
    }

    /**
     * Get the stock flow associated with this sales item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function stock_flow()
    {
        return $this->morphOne(ItemStockFlow::class, 'source');
    }

    /**
     * Get the commission flow associated with this sales item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function commission_flow()
    {
        return $this->morphOne(EmployeeSalesCommissionLog::class, 'source');
    }
}
