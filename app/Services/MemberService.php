<?php

namespace App\Services;

use App\Models\Member;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class MemberService
{


    /**
     * Create a new member.
     *
     * @param array $param
     * @return Member
     */
    public function create(array $param): Member
    {
        return Member::create($param);
    }
    /**
     * Create a new member.
     *
     * @param array $param
     * @return Member
     */
    public function getData($search, $page, $perPage): LengthAwarePaginator
    {
        $key = "member:page[$page]:perPage[$perPage]:search[$search]";

        $data = Cache::remember($key, now()->addHours(1), function () use ($search, $key, $perPage) {
            $query = Member::query();
            $listKey = "cache_key_list_member";
            $list = Cache::get($listKey, []);
            $list[] = $key;
            Cache::put($listKey, $list, 600);

            if (isset($param['search'])) {
                $search = $param['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                });
            }

            return $query->paginate($perPage);
        });
        return $data;
    }

    /**
     * Update an existing member.
     *
     * @param Member $model
     * @param array $param
     * @return Member
     */
    public function update(Member $model, array $param): Member
    {
        $model->update($param);
        return $model;
    }

    /**
     * Delete a member.
     *
     * @param Member $model
     * @return bool|null
     * @throws \Exception
     */
    public function destroy(Member $model): ?bool
    {
        return $model->delete();
    }


    /**
     * Retrieves a collection of sales associated with a given member.
     *
     * @param  \App\Models\Member $member The member whose sales records are to be retrieved.
     * @param  array $param An array of parameters used to filter the sales records.
     *                      The 'dateRange' parameter can be used to filter records
     *                      within a specific date range.
     * @return \Illuminate\Support\Collection Returns a collection of sales records.
     *                                        If 'dateRange' is provided and has exactly
     *                                        two elements, it will return records between
     *                                        those dates.
     */
    public function getSalesList($member, $page, $perPage, $dateRange): Collection
    {
        $key = "member:id[$member->id]page[$page]:perPage[$perPage]:dateRange[$dateRange]";

        $data = Cache::remember($key, now()->addDay(1), function () use ($dateRange, $key, $member) {
            $query = $member->sales();
            $listKey = "cache_key_list_member_{$member->id}_sales_list";
            $list = Cache::get($listKey, []);
            $list[] = $key;
            Cache::put($listKey, $list, 600);
            if (isset($dateRange) && count($dateRange) === 2)
                $query = $query->whereBetween('created_at', $dateRange);

            return $query->get();
        });
        return $data;
    }

    /**
     * Retrieves a collection of member's sales point logs.
     *
     * @param Member $member The member object whose point logs are to be retrieved.
     * @param array $param An associative array of parameters used to filter the results.
     *                     The 'dateRange' key can be set to an array with two dates (start and end) to filter the logs within that range.
     * @return Collection Returns a Laravel Collection of the member's sales point logs, ordered by descending sales count.
     */
    public function getMemberPointLog($member, $page, $perPage, $dateRange): Collection
    {
        $key = "member:id[$member->id]page[$page]:perPage[$perPage]:dateRange[$dateRange]";

        $data = Cache::remember($key, now()->addDay(1), function () use ($dateRange, $key, $member) {
            $listKey = "cache_key_list_member_{$member->id}_log_list";
            $list = Cache::get($listKey, []);
            $list[] = $key;
            Cache::put($listKey, $list, 600);
            $query = $member->sales_point()->whereHas('sales', function ($query) use ($dateRange) {
                if (isset($dateRange) && count($dateRange) === 2)
                    $query = $query->whereBetween('created_at', $dateRange);
            })->orderByDesc('sales_count');


            return $query->get();
        });
        return $data;
    }

    /**
     * Retrieves a collection of 'royal' employees based on sales data.
     *
     * This function queries the 'Member' model, filtering for members who have associated 'sales' records.
     * It allows for an optional date range filter to be applied to the 'sales' records, which is passed via the `$param` array.
     * If the 'dateRange' key exists within `$param` and contains exactly two dates, it will filter 'sales' created within that range.
     * The resulting query is then ordered in descending order based on the count of 'sales' for each member.
     * 
     * @param array $param Associative array of parameters used for filtering the query. Expected keys:
     *                     - 'dateRange': An array containing two dates to filter the 'sales' by their 'created_at' timestamps.
     * @return Collection A Laravel Collection containing members sorted by their associated sales count in descending order.
     */
    public function getListOfLoyalMember($page, $perPage, $dateRange): Collection
    {
        $key = "member:royal:page[$page]:perPage[$perPage]:dateRange[$dateRange]";

        $data = Cache::remember($key, now()->addDay(1), function () use ($dateRange, $key) {
            $listKey = "cache_key_list_member_royal_list";
            $list = Cache::get($listKey, []);
            $list[] = $key;
            Cache::put($listKey, $list, 600);
            $query = Member::whereHas('sales', function ($query) use ($dateRange) {
                if (isset($dateRange) && count($dateRange) === 2)
                    $query = $query->whereBetween('created_at', $dateRange);
            })->withCount('sales')
                ->orderByDesc('sales_count');


            return $query->get();
        });
        return $data;
    }
}
