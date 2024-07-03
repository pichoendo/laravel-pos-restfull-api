<?php

namespace App\Services;

use App\Models\Category;

class CategoryService
{
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
}
