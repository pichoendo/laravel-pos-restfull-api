<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryService
{

    /**
     * Retrieves paginated category data based on search parameters.
     * 
     * @param array $param Associative array of parameters where 'search' key can be used to filter results.
     * @param int $perPage The number of records to return per page.
     * @return LengthAwarePaginator A paginator instance containing the results.
     */
    public function getData($param, $page, $perPage): LengthAwarePaginator
    {
        $query = Category::query();

        if (isset($param['search'])) {
            $search = $param['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new category.
     *
     * @param array $param
     * @return Category
     */
    public function create(array $param): Category
    {
        return Category::create($param);
    }

    /**
     * Update an existing category.
     *
     * @param Category $model
     * @param array $param
     * @return Category
     */
    public function update(Category $model, array $param): Category
    {
        $model->update($param);
        return $model;
    }

    /**
     * Delete a category.
     *
     * @param Category $model
     * @return bool|null
     * @throws \Exception
     */
    public function destroy(Category $model): ?bool
    {
        return $model->delete();
    }

    /**
     * Retrieves a list of sales items associated with a given category.
     * 
     * @param Category $category The category for which the sales items are to be retrieved.
     * @param array $param Additional parameters to filter the sales items.
     * - 'dateRange': An array containing start and end dates to filter items by creation date.
     * - 'member': A member identifier to filter items by member.
     * 
     * @return Category A collection of sales items that belong to the specified category and match the given parameters.
     */
    public function getListOfItems(Category $category, array $param): Category
    {
        $query = $category->items();
        if (isset($param['isLowStock']))
            if ($param['isLowStock'] == 1)
                $query = $query->filter(function ($category) {
                    return $category->stock_count < 10;
                });

        if (isset($param['member']))
            $query = $query->where('member', $param['member']);

        return $query->get();
    }
}
