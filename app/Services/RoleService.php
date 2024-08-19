<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RoleService
{
    /**
     * Create a new role.
     *
     * @param array $param
     * @return Role
     */
    public function create(array $param): Role
    {
        return Role::create($param);
    }

    /**
     * Retrieve paginated role data with caching.
     *
     * @param string|null $search The search term to filter roles by name.
     * @param int $page The page number for pagination.
     * @param int $perPage The number of items per page.
     * @return LengthAwarePaginator The paginated data.
     */
    public function getData(?string $search, int $page, int $perPage): LengthAwarePaginator
    {
        // Generate a unique cache key based on page, perPage, and search parameters
        $key = "Role:page[$page]:perPage[$perPage]:search[$search]";

        // Retrieve data from cache or execute the query and store the result
        $data = Cache::remember($key, now()->addDay(1), function () use ($search, $key, $perPage) {
            $query = Role::query();

            // Maintain a list of cache keys for easy invalidation later
            $listKey = "cache_key_list_role";
            $list = Cache::get($listKey, []);

            if (!in_array($key, $list)) {
                $list[] = $key;
                Cache::put($listKey, $list, 600); // Store the list for 10 minutes
            }

            // Apply search filter if a search term is provided
            if ($search) {
                $query->where('name', 'LIKE', "%{$search}%");
            }

            // Return paginated results
            return $query->paginate($perPage);
        });

        return $data;
    }

    /**
     * Update an existing role.
     *
     * @param Role $model
     * @param array $param
     * @return Role
     */
    public function update(Role $model, array $param): Role
    {
        $model->update($param);
        return $model;
    }

    /**
     * Delete a role.
     *
     * @param Role $model
     * @return bool|null
     * @throws \Exception
     */
    public function destroy(Role $model): ?bool
    {
        try {
            return $model->delete();
        } catch (\Exception $e) {
            Log::error("Error deleting role: " . $e->getMessage());
            throw $e;
        }
    }
}
