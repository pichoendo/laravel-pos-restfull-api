<?php

namespace App\Services;

use App\Models\Member;

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
}
