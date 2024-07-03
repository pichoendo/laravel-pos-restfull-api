<?php

namespace App\Models;

use App\Services\CodeGeneratorService;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *      schema="Item",
 *      title="Item",
 *      description="Item model",
 *      @OA\Property(
 *          property="id",
 *          type="integer",
 *          description="Item ID",
 *          example="1"
 *      ),
 *      @OA\Property(
 *          property="uuid",
 *          type="string",
 *          description="UUID of the item",
 *          example="123e4567-e89b-12d3-a456-426614174000"
 *      ),
 *      @OA\Property(
 *          property="name",
 *          type="string",
 *          description="Name of the item",
 *          example="Sample Item"
 *      ),
 *      @OA\Property(
 *          property="image",
 *          type="string",
 *          description="URL to the image of the item",
 *          example="http://example.com/item.jpg"
 *      ),
 *      @OA\Property(
 *          property="category_id",
 *          type="integer",
 *          description="Category ID of the item",
 *          example="1"
 *      ),
 *      @OA\Property(
 *          property="price",
 *          type="number",
 *          format="decimal",
 *          description="Price of the item",
 *          example="19.99"
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          type="string",
 *          format="date-time",
 *          description="Date and time when the item was created",
 *          example="2024-07-03 12:00:00"
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          type="string",
 *          format="date-time",
 *          description="Date and time when the item was last updated",
 *          example="2024-07-03 12:30:00"
 *      ),
 *      @OA\Property(
 *          property="deleted_at",
 *          type="string",
 *          format="date-time",
 *          description="Date and time when the item was soft deleted",
 *          example="2024-07-03 12:45:00"
 *      ),
 *      @OA\Property(
 *          property="stock_count",
 *          type="integer",
 *          description="Computed attribute: Total stock count of the item",
 *          example="100"
 *      )
 * )
 */
class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['stock_count'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'image_file_id',
        'category_id',
        'price',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2', // Example casting for price to decimal with 2 decimal places
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
            $model->code = app(CodeGeneratorService::class)->generateCode("ITM", Item::class);
            $model->created_by = auth()->user()->id ?? null;
            $model->updated_by = auth()->user()->id ?? null;
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->user()->id ?? null;
        });
    }

    /**
     * Get the category that owns the item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the category that owns the item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function imageFile()
    {
        return $this->belongsTo(ImageFile::class);
    }

    /**
     * Get the stocks of the item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function item_stocks()
    {
        return $this->hasMany(ItemStock::class);
    }

    /**
     * Get the computed attribute for stock count of the item.
     *
     * @return int
     */
    public function getStockCountAttribute()
    {
        return $this->item_stocks()->sum('qty');
    }
}
