<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;


class CategoryService
{
    /**
     * Retrieves paginated category data based on search parameters.
     *
     * This method fetches categories with pagination and optional search filtering.
     * It uses caching to improve performance by storing and reusing query results.
     *
     * @param string $search The search term to filter categories by name.
     * @param int $page The page number for pagination.
     * @param int $perPage The number of records to return per page.
     * @return LengthAwarePaginator A paginator instance containing the results.
     */
    public function getData(string $search, int $page, int $perPage): LengthAwarePaginator
    {
        // Cache key for storing and retrieving paginated categories with filters
        $key = "category:page[$page]:perPage[$perPage]:search[$search]";

        $data = Cache::remember($key, now()->addHours(1), function () use ($search, $perPage, $key) {
            // Start query for categories
            $query = Category::query();

            // Cache key for storing and retrieving the list of cache keys for categories
            $listKey = "cache_key_list_category";
            $list = Cache::get($listKey, []);

            // Add current cache key to the list if it's not already present
            if (!in_array($key, $list)) {
                $list[] = $key;
            }
            Cache::put($listKey, $list, 600);

            // Apply search filter if a search term is provided
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                });
            }

            // Return paginated results
            return $query->paginate($perPage);
        });

        return $data;
    }

    /**
     * Create a new category.
     *
     * This method creates a new category record in the database with the provided parameters.
     *
     * @param array $param Associative array of category attributes.
     * @return Category The created Category model instance.
     */
    public function create(array $param): Category
    {
        return Category::create($param);
    }

    /**
     * Update an existing category.
     *
     * This method updates the specified category with the provided parameters.
     *
     * @param Category $model The Category model instance to update.
     * @param array $param Associative array of attributes to update.
     * @return Category The updated Category model instance.
     */
    public function update(Category $model, array $param): Category
    {
        $model->update($param);
        return $model;
    }

    /**
     * Delete a category.
     *
     * This method deletes the specified category from the database.
     *
     * @param Category $model The Category model instance to delete.
     * @return bool|null True if the category was deleted, false otherwise, or null if deletion fails.
     * @throws \Exception If an exception occurs during deletion.
     */
    public function destroy(Category $model): ?bool
    {
        return $model->delete();
    }

    /**
     * Retrieve a paginated list of items for a specific category with optional filters.
     *
     * This method fetches items associated with the specified category, applying filters for low stock
     * and search terms. The results are paginated and cached to improve performance.
     *
     * @param Category $category The Category model instance whose items are to be retrieved.
     * @param string $search The search term to filter items by name.
     * @param int $page The page number for pagination.
     * @param int $perPage The number of records to return per page.
     * @param bool $isLowStock A flag to filter items with low stock (true) or not (false).
     * @return LengthAwarePaginator A paginator instance containing the results.
     */
    public function getListOfItems(Category $category, string $search, int $page, int $perPage, bool $isLowStock): LengthAwarePaginator
    {
        // Cache key for storing and retrieving paginated items with filters
        $key = "categoryItemsByid:id[{$category->id}]:page[$page]:perPage[$perPage]:search[$search]:lowStock[{$isLowStock}]";

        $data = Cache::remember($key, now()->addHours(1), function () use ($category, $isLowStock, $perPage, $search, $key) {
            // Start query for items related to the specified category
            $query = $category->items();

            // Cache key for storing and retrieving the list of cache keys for this category
            $listKey = "cache_key_list_category_items_By_id_{$category->id}";
            $list = Cache::get($listKey, []);

            // Add current cache key to the list if it's not already present
            if (!in_array($key, $list)) {
                $list[] = $key;
            }
            Cache::put($listKey, $list, 600);

            // Apply low stock filter if requested
            if ($isLowStock) {
                $query = $query->filter(function ($item) {
                    return $item->stock_count < 10;
                });
            }

            // Apply search filter if a search term is provided
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                });
            }

            // Return paginated results
            return $query->paginate($perPage);
        });

        return $data;
    }
}
