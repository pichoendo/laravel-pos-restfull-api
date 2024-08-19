<?php

namespace App\Models;


use App\Services\CodeGeneratorService;
use App\Traits\Cacheable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes, Cacheable;

    protected $appends = ["stock_count"];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "name",
        'uuid',
        "image_file_id",
        "category_id",
        "price",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "price" => "decimal:2", // Example casting for price to decimal with 2 decimal places
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return "uuid";
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
            $model->clearCache(["cache_key_list_category_items_By_id_$model->category_id", "cache_key_list_item_out_of_stock"]);
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->user()->id ?? null;
            $arr = ["cache_key_list_category_items_By_id_$model->category_id", "cache_key_list_item_top_selling", "cache_key_list_item_out_of_stock"];
            if ($model->getOriginal('category_id') != $model->category_id)
                $arr[] = "cache_key_list_category_items_By_id_{$model->getOriginal('category_id')}";

            $model->clearCache($arr);
        });

        static::deleted(function ($model) {
            $model->clearCache(["cache_key_list_category_items_By_id_$model->category_id", "cache_key_list_item_top_selling", "cache_key_list_item_out_of_stock"]);
        });
    }

    /**
     * Get the category that owns the item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class, "category_id", "id");
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
     * Get the list of its sales item .
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function item_on_sales()
    {
        return $this->hasMany(SalesItem::class);
    }

    /**
     * Get the computed attribute for stock count of the item.
     *
     * @return int
     */
    public function getStockCountAttribute()
    {
        return $this->item_stocks()->sum("qty");
    }

    /**
     * Get the computed attribute for sales count of the item.
     *
     * @return int
     */
    public function getSalesCountAttribute()
    {
        return $this->item_on_sales()->sum("qty");
    }
}
