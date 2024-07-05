<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
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

    public function getData($param, $page, $perPage): LengthAwarePaginator
    {
        $query = Item::query();

        if (isset($param['search'])) {
            $search = $param['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
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

    /**
     * Retrieves the stock information for a given item.
     *
     * @param  \App\Models\Item  $item  The item for which stock information is needed.
     * @return \Illuminate\Database\Eloquent\Collection  A collection of item stock records.
     */
    public function getItemStocks($item): Collection
    {
        $query = $item->item_stocks()->where(function ($query) {
        });


        return $query->get();
    }


    /**
     * Retrieves the stock information for a given item.
     *
     * @param  \App\Models\Item  $item  The item for which stock information is needed.
     * @return \Illuminate\Database\Eloquent\Collection  A collection of item stock records.
     */
    public function getTopSellingItems($params): Collection
    {
        $query = Item::withCount('sale')
            ->orderByDesc('sale_count')
            ->whereHas(function ($query) use ($params) {
                $query = $query->whereHas(function ($query) use ($params) {
                    if (isset($params['dateRange']) && count($params['dateRange']) === 2)
                        $query = $query->whereBetween('created_at', $params['dateRange']);
                });
            });


        return $query->get();
    }

    /**
     * Retrieves the stock information for a given item.
     *
     * @param  \App\Models\Item  $item  The item for which stock information is needed.
     * @return \Illuminate\Database\Eloquent\Collection  A collection of item stock records.
     */
    public function getOutOfStockItems(): Collection
    {
        $query = Item::withSum('item_stocks', 'qty')
            ->having('item_stocks_sum_qty', '<', 10);

        return $query->get();
    }
}
