<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Support\Facades\DB;

class ItemService
{
    private ItemStockService $itemStockService;

    public function __construct(ItemStockService $itemStockService)
    {
        $this->itemStockService = $itemStockService;
    }

    /**
     * Create a new item and its associated stock.
     *
     * @param array $param
     * @return Item
     */
    public function create(array $param): Item
    {
        return DB::transaction(function () use ($param) {
            $item = Item::create($param);
            $this->itemStockService->createStock($item->id, $param['cogs'], $param['qty']);
            return $item;
        });
    }

    /**
     * Update an existing item.
     *
     * @param Item $model
     * @param array $param
     * @return Item
     */
    public function update(Item $model, array $param): Item
    {
        return DB::transaction(function () use ($model, $param) {
            $model->update($param);
            return $model;
        });
    }

    /**
     * Delete an item.
     *
     * @param Item $model
     * @return bool|null
     * @throws \Exception
     */
    public function destroy(Item $model): ?bool
    {
        return $model->delete();
    }
}
