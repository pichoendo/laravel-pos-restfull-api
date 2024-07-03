<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     title="ItemStockOperation",
 *     description="Item Stock Operation Model",
 * )
 */
class ItemStockOperation extends Model
{
    // Your existing code here...

    /**
     * @OA\Property(
     *     property="item_id",
     *     type="integer",
     *     description="The ID of the item",
     * )
     *
     * @OA\Property(
     *     property="qty",
     *     type="integer",
     *     description="The quantity of the item",
     * )
     *
     * @OA\Property(
     *     property="type",
     *     type="string",
     *     description="The type of operation (e.g., 'add', 'subtract')",
     * )
     * */
    protected $fillable = [
        'item_id',
        'qty',
        'type',
    ];

    /**
     * This method defines routines to be executed when the model is created or updated.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->created_by = auth()->user()->id ?? null;
        });
    }

    /**
     * Get the item that this operation affects.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the stock flow associated with this operation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function stock_flow()
    {
        return $this->morphOne(ItemStockFlow::class, 'source');
    }
}
