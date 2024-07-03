<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Http\Resources\MemberResource;
use App\Http\Responses\APIResponse;
use App\Services\MemberService;
use Exception;
use Illuminate\Http\Request;

class MemberController extends Controller
{

    private MemberService $memberService;

    public function __construct(MemberService $memberService)
    {
        $this->memberService = $memberService;
    }

    /**
     * @OA\Get(
     *     path="/api/members",
     *     summary="List members",
     *     description="Get a paginated list of members",
     *     operationId="getMembers",
     *     tags={"Members"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Members fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Member")),
     *             @OA\Property(property="message", type="string", example="Members fetched successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to fetch members. Please try again later.")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $query = Member::query();
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        $query = $query->paginate($perPage);

        return APIResponse::success(MemberResource::collection($query), 'Members fetched successfully', 200);
    }


    /**
     * @OA\Post(
     *     path="/api/members",
     *     summary="Create member",
     *     description="Create a new member",
     *     operationId="storeMember",
     *     tags={"Members"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreMemberRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Member created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Member"),
     *             @OA\Property(property="message", type="string", example="Member created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to create member. Please try again later.")
     *         )
     *     )
     * )
     */
    public function store(StoreMemberRequest $request)
    {
        $param = $request->validated();
        try {
            $data = $this->memberService->create($param);
            return APIResponse::success(new MemberResource($data), 'Member created successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error($ex->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/members/{member}",
     *     summary="Get member",
     *     description="Fetch a specific member by ID",
     *     operationId="showMember",
     *     tags={"Members"},
     *     @OA\Parameter(
     *         name="member",
     *         in="path",
     *         description="Member ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Member fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Member"),
     *             @OA\Property(property="message", type="string", example="Member fetched successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to fetch member. Please try again later.")
     *         )
     *     )
     * )
     */
    public function show(Member $Member)
    {
        return APIResponse::success(new MemberResource($Member), 'Member fetched successfully', 200);
    }

    /**
     * @OA\Put(
     *     path="/api/members/{member}",
     *     summary="Update member",
     *     description="Update an existing member",
     *     operationId="updateMember",
     *     tags={"Members"},
     *     @OA\Parameter(
     *         name="member",
     *         in="path",
     *         description="Member ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateMemberRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Member updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Member"),
     *             @OA\Property(property="message", type="string", example="Member updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to update member. Please try again later.")
     *         )
     *     )
     * )
     */
    public function update(UpdateMemberRequest $request, Member $Member)
    {
        $param = $request->validated();
        try {
            $data = $this->memberService->update($Member, $param);
            return APIResponse::success(new MemberResource($data), 'Member updated successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error($ex->getMessage(), 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/members/{member}",
     *     summary="Delete member",
     *     description="Delete a specific member by ID",
     *     operationId="deleteMember",
     *     tags={"Members"},
     *     @OA\Parameter(
     *         name="member",
     *         in="path",
     *         description="Member ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Member deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Member deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to delete member. Please try again later.")
     *         )
     *     )
     * )
     */
    public function destroy(Member $Member)
    {
        try {
            $this->memberService->destroy($Member);
            return APIResponse::success(null, 'Member deleted successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error($ex->getMessage(), 500);
        }
    }
}
