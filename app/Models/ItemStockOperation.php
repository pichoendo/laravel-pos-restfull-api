<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemStockOperation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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
    public function stockFlow()
    {
        return $this->morphOne(ItemStockFlow::class, 'source');
    }
}
