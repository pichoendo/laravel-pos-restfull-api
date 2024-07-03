<?php

namespace App\Services;

use App\Models\Role;
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