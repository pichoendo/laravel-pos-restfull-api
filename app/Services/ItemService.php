<?php

namespace App\Services;

use App\Models\Item;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ItemService
{
    public function __construct(
        public ItemStockService $itemStockService,
        public FileService $fileService
    ) {}

    /**
     * Get paginated data based on search criteria.
     *
     * @param string $search The search term.
     * @param int $page The page number for pagination.
     * @param int $perPage Number of items per page.
     * @return LengthAwarePaginator Paginated data.
     */
    public function getData(string $search, int $page, int $perPage): LengthAwarePaginator
    {
        // Construct a unique cache key based on parameters
        $key = "item:page[$page]:perPage[$perPage]:search[$search]";

        // Retrieve data from cache or fetch it if not cached
        $data = Cache::remember($key, now()->addHours(1), function () use ($perPage, $key, $search) {
            $query = Item::query();

            // Maintain a list of cache keys
            $listKey = "cache_key_list_item";
            $list = Cache::get($listKey, []);
            $list[] = $key;
            Cache::put($listKey, $list, 600);

            // Apply search filter if provided
            if (!empty($search))
                $query->where('name', 'LIKE', "%{$search}%");


            return $query->paginate($perPage);
        });

        return $data;
    }

    /**
     * Create a new item and its associated stock.
     *
     * @param array $param Parameters for creating the item.
     * @return Item The created item.
     */
    public function create(array $param): Item
    {
        return DB::transaction(function () use ($param) {
            // Create the item
            $item = Item::create($param);

            // Handle image file if present
            if (isset($param['image_file'])) {
                $file = $this->fileService->upload($param['image_file']);
                if ($file) {
                    // Associate the uploaded file with the item
                    $item->imageFile()->save($file);
                }
            }
            // Create stock for the item
            $this->itemStockService->createStock($item->id, $param['cogs'], $param['qty']);

            return $item;
        });
    }

    /**
     * Update an existing item.
     *
     * @param Item $model The item to update.
     * @param array $param Parameters for updating the item.
     * @return Item The updated item.
     */
    public function update(Item $model, array $param): Item
    {
        return DB::transaction(function () use ($model, $param) {
            // update the item
            $model->update($param);

            // Handle new image file if present
            if (isset($param['image_file'])) {

                if (!empty($model->imageFile)) {
                    // delete old image file
                    $this->fileService->deleteFile($model->imageFile);
                }
                // store new image file
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
     * @param Item $model The item to delete.
     * @return bool|null True if deletion was successful, null otherwise.
     * @throws \Exception If deletion fails.
     */
    public function destroy(Item $model): ?bool
    {
        return $model->delete();
    }

    /**
     * Retrieve the stock information for a given item.
     *
     * @param Item $item The item for which stock information is needed.
     * @return Collection A collection of item stock records.
     */
    public function getItemStocks(Item $item): Collection
    {
        // Define a unique cache key based on the item's ID
        $key = "itemStock:id[{$item->id}]";

        //  get cached data or store it if not present
        $data = Cache::remember($key, now()->addHours(1), function () use ($item) {
            // Fetch and return the related item stocks
            return $item->item_stocks; // Assumes item_stocks is a defined relationship
        });

        return $data; // Return the cached or freshly fetched data
    }

    /**
     * Retrieve the top-selling items within a date range.
     *
     * @param array $dateRange The date range for filtering.
     * @return Collection A collection of top-selling items.
     */
    public function getTopSellingItems(array $dateRange, int $page, int $perPage): Collection
    {
        // Generate a unique cache key based on the date range
        $key = "itemTopSelling:page[$page]:perPage[$perPage]:dateRange[" . implode(',', $dateRange) . "]";

        //  get cached data or store it if not present
        $data = Cache::remember($key, now()->addHours(1), function () use ($key, $perPage, $dateRange) {

            $listKey = "cache_key_list_item_top_selling";

            $list = Cache::get($listKey, []);
            $list[] = $key;
            Cache::put($listKey, $list, 600);

            return Item::withCount('sales')
                ->orderByDesc('sales_count')
                ->whereHas('sales', function ($query) use ($dateRange) {
                    if (count($dateRange) === 2) {
                        $query->whereBetween('created_at', $dateRange);
                    }
                })
                ->paginate($perPage);
        });

        return $data;
    }

    /**
     * Retrieve out-of-stock items.
     *
     * @param int $page The page number for pagination.
     * @param int $perPage Number of items per page.
     * @return Collection A collection of out-of-stock items.
     */
    public function getOutOfStockItems(int $page, int $perPage): Collection
    {
        $key = "itemOutOfStock:page[$page]:perPage[$perPage]";

        $data = Cache::remember($key, now()->addHour(1), function () use ($key) {
            // Manage the list of cache keys
            $listKey = "cache_key_list_item_out_of_stock";
            $list = Cache::get($listKey, []);
            $list[] = $key;
            Cache::put($listKey, $list, 600);

            return Item::withSum('item_stocks', 'qty')
                ->having('item_stocks_sum_qty', '<', 10)
                ->get();
        });

        return $data;
    }
}
