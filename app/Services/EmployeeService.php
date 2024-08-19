<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class EmployeeService
{
    /**
     * Create a new employee record.
     *
     * @param array $param An associative array containing employee attributes.
     * @return Employee The newly created Employee model instance.
     */
    public function create(array $param): Employee
    {
        return Employee::create($param);
    }

    /**
     * Retrieve a paginated list of employees with optional search filtering.
     *
     * This method retrieves a paginated list of employees, optionally filtering by
     * a search term that matches employee names. The results are cached for
     * efficient repeated access. If the data is not available in the cache, it
     * is fetched from the database and then cached.
     *
     * @param string $search An optional search term to filter employee names. Default is an empty string.
     * @param int $page The page number for pagination. Default is 1.
     * @param int $perPage The number of items per page. Default is 10.
     * 
     * @return LengthAwarePaginator A paginated list of employees.
     */
    public function getData(string $search = "", int $page = 1, int $perPage = 10): LengthAwarePaginator
    {
        // Generate a unique cache key based on search term, pagination parameters, and the page number.
        $key = "Employee:page[{$page}]:perPage[{$perPage}]:search[" . urlencode($search) . "]";

        // Attempt to retrieve the data from cache or fetch it from the database if not found in the cache.
        $data = Cache::remember($key, now()->addHours(1), function () use ($search, $perPage, $key) {
            // Start building the query to fetch employee records.
            $query = Employee::query();

            // Generate a list key to keep track of all cache keys related to employee records.
            $listKey = "cache_key_list_employee";

            // Retrieve the current list of cache keys from the cache, or initialize an empty array if not found.
            $list = Cache::get($listKey, []);

            // Add the current cache key to the list and update the cache with the new list.
            $list[] = $key;
            Cache::put($listKey, $list, 600); // Cache the list of keys for 10 minutes.

            // Apply search filter if provided.
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                });
            }

            // Execute the query with pagination and return the result.
            return $query->paginate($perPage);
        });

        // Return the retrieved data (from cache or database).
        return $data;
    }

    /**
     * Check if an employee with the given email address exists.
     *
     * @param string $email The email address to check.
     * @return bool True if no employee with the given email exists, otherwise false.
     */
    public function doesEmployeeWithEmailExist($email)
    {
        return Employee::where('email', $email)->count() == 0;
    }

    /**
     * Update an existing employee record.
     *
     * @param Employee $model The Employee model instance to update.
     * @param array $param An associative array containing updated employee attributes.
     * @return Employee The updated Employee model instance.
     */
    public function update(Employee $model, array $param): Employee
    {
        $model->update($param);
        return $model;
    }

    /**
     * Delete an employee record.
     *
     * @param Employee $model The Employee model instance to delete.
     * @return bool|null True if deletion was successful, false otherwise. Returns null if the model does not exist.
     */
    public function destroy(Employee $model): ?bool
    {
        return $model->delete();
    }

    /**
     * Retrieve the sales list for a specific employee.
     *
     * @param Employee $employee The Employee model instance.
     * @param int $page The page number for pagination.
     * @param int $perPage The number of items per page.
     * @param array $dateRange The date range for filtering sales records.
     * @return Collection The collection of sales records.
     */
    public function getSalesList($employee, $page, $perPage, $dateRange): Collection
    {
        // Generate a unique cache key based on employee ID, pagination parameters, and date range.
        $key = "Employee:id[{$employee->id}]:managed:sales:page[{$page}]:perPage[{$perPage}]:dateRange[" . implode(',', $dateRange) . "]";

        // Attempt to retrieve the data from cache or fetch it from the database if not found in the cache.
        $data = Cache::remember($key, now()->addHours(1), function () use ($employee, $dateRange, $key) {
            // Start building the query to fetch sales records for the employee.
            $query = $employee->sales();

            // Generate a list key to keep track of all cache keys related to the employee's sales records.
            $listKey = "cache_key_list_employee_{$employee->id}_managed_sales";

            // Retrieve the current list of cache keys from the cache, or initialize an empty array if not found.
            $list = Cache::get($listKey, []);

            // Add the current cache key to the list and update the cache with the new list.
            $list[] = $key;
            Cache::put($listKey, $list, 600); // Cache the list of keys for 10 minutes.

            // Apply date range filter if provided.
            if (isset($dateRange) && count($dateRange) === 2) {
                $query->whereBetween('created_at', $dateRange);
            }

            // Execute the query and return the result.
            return $query->get();
        });

        // Return the retrieved data (from cache or database).
        return $data;
    }

    /**
     * Retrieve a list of royal employees based on the given parameters.
     *
     * @param int $page The page number for pagination.
     * @param int $perPage The number of items per page.
     * @param array $dateRange The date range for filtering employee records.
     * @return Collection The collection of royal employees.
     */
    public function getListOfRoyalEmployees($page, $perPage, $dateRange): Collection
    {
        // Generate a unique cache key based on page number, items per page, and date range.
        $key = "Employee:royal:page[$page]:perPage[$perPage]:dateRange[$dateRange]";

        // Attempt to retrieve the data from cache or fetch it from the database if not found in the cache.
        $data = Cache::remember($key, now()->addHours(1), function () use ($dateRange, $perPage, $key) {

            // Generate a list key to keep track of all cache keys related to the royal employees list.
            $listKey = "cache_key_list_employee_royal";

            // Retrieve the current list of cache keys from the cache, or initialize an empty array if not found.
            $list = Cache::get($listKey, []);

            // Add the current cache key to the list and update the cache with the new list.
            $list[] = $key;
            Cache::put($listKey, $list, 600); // Cache the list of keys for 10 minutes.

            // Build the query to fetch the royal employees, filtered by date range and ordered by sales count.
            $query = Employee::whereHas('sales', function ($query) use ($dateRange) {
                // Apply date range filter if provided.
                if (isset($dateRange))
                    $query->whereBetween('created_at', $dateRange);
            })->withCount('sales')
                ->orderByDesc('sales_count'); // Order results by sales count in descending order.

            return $query->paginate($perPage);
        });

        // Return the retrieved data (from cache or database).
        return $data;
    }

    /**
     * Retrieve the commission log for a specific employee.
     *
     * @param Employee $employee The Employee model instance.
     * @param array $dateRange The date range for filtering commission records.
     * @param int $page The page number for pagination.
     * @param int $perPage The number of items per page.
     * @return Collection The collection of commission log records.
     */
    public function getEmployeeCommissionLog($employee, $page, $perPage, $dateRange): Collection
    {
        // Generate a unique cache key based on employee ID, page number, items per page, and date range.
        $key = "Employee:id[$employee->id]:commission:log:page[$page]:perPage[$perPage]:dateRange[$dateRange]";

        // Attempt to retrieve the data from cache, or fetch it from the database if not cached.
        $data = Cache::remember($key, now()->addHours(1), function () use ($employee, $dateRange, $perPage, $key) {

            // Generate a list key to keep track of all cache keys related to this employee's commission log.
            $listKey = "cache_key_list_employee_{$employee->id}_commission_log";

            // Retrieve the current list of cache keys from the cache, or initialize an empty array if not found.
            $list = Cache::get($listKey, []);

            // Add the current cache key to the list and update the cache with the new list.
            $list[] = $key;
            Cache::put($listKey, $list, 600); // Cache the list of keys for 10 minutes.

            // Build the query to fetch the commission log records, filtering by date range.
            $query = $employee->commisionLog()->whereHas('sales', function ($query) use ($dateRange) {
                // Apply date range filter if provided.
                if (isset($dateRange))
                    $query->whereBetween('created_at', $dateRange);
            })->orderByDesc('sales_count'); // Order results by sales count in descending order.

            // Execute the query and return the result.
            return $query->paginate($perPage);
        });

        // Return the retrieved data (from cache or database).
        return $data;
    }
}
