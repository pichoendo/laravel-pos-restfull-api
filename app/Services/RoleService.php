<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
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

    public function getData($param, $page, $perPage): LengthAwarePaginator
    {
        $query = Role::query();

        if (isset($param['search'])) {
            $search = $param['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
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
