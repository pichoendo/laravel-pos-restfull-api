<?php

namespace App\Http\Controllers;

use App\Http\Resources\SalesResource;
use App\Http\Responses\APIResponse;
use App\Models\Member;
use Illuminate\Http\Request;

class MemberSalesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Member $member)
    {
        $perPage = $request->input('per_page', 10);

        // Fetch sales related to the member using Eloquent relationship
        $query = $member->sales()->paginate($perPage);

        // Return success response with paginated sales data
        return APIResponse::success(SalesResource::collection($query), 'Sales fetched successfully', 200);
    }
}
