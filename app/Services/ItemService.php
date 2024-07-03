<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Support\Facades\DB;

class ItemService
{
    private ItemStockService $itemStockService;
    private FileService $fileService;

    public function __construct(ItemStockService $itemStockService, FileService $fileService)
    {
        $this->fileService = $fileService;
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
            if (isset($param['image_file'])) {
                $file = $this->fileService->upload($param['image_file']);
                if ($file) {
                    $file->item()->save($item);
             
                }
            }
      
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
            if (isset($param['image_file'])) {
                if (!empty($model->imageFile)) {
                    $this->fileService->deleteFile($model->imageFile);
                }

                $file = $this->fileService->upload($param['image_file']);
                if ($file) {
                    $model->imageFile()->save($file);
                }
            }
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
