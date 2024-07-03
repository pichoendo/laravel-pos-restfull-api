<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      schema="ItemStockFlow",
 *      title="ItemStockFlow",
 *      description="ItemStockFlow model",
 *      @OA\Property(
 *          property="id",
 *          type="integer",
 *          description="ItemStockFlow ID",
 *          example="1"
 *      ),
 *      @OA\Property(
 *          property="item_stock_id",
 *          type="integer",
 *          description="ID of the item stock associated with this flow",
 *          example="1"
 *      ),
 *      @OA\Property(
 *          property="qty",
 *          type="integer",
 *          description="Quantity of items involved in the flow",
 *          example="10"
 *      ),
 *      @OA\Property(
 *          property="source_type",
 *          type="string",
 *          description="Type of the source model that initiated the stock flow (polymorphic)",
 *          example="App\Models\Order"
 *      ),
 *      @OA\Property(
 *          property="source_id",
 *          type="integer",
 *          description="ID of the source model instance that initiated the stock flow (polymorphic)",
 *          example="1"
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          type="string",
 *          format="date-time",
 *          description="Date and time when the stock flow was created",
 *          example="2024-07-03 12:00:00"
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          type="string",
 *          format="date-time",
 *          description="Date and time when the stock flow was last updated",
 *          example="2024-07-03 12:30:00"
 *      )
 * )
 */
class ItemStockFlow extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'item_stock_id',
        'qty',
    ];

    /**
     * Get the source model that initiated the stock flow.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function source()
    {
        return $this->morphTo();
    }
}
