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
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 0);
            $data = $this->memberService->getData($request->all(), $page, $perPage);
            return APIResponse::success(MemberResource::collection($data), 'Fetch successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to create category. Please try again later.', 500);
        }
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
    /**
     * @OA\Get(
     *     path="/api/members/{member}/sales",
     *     operationId="getMemberSalesList",
     *     tags={"Members"},
     *     summary="Get sales list of a member",
     *     description="Returns sales list of a specific member.",
     *     @OA\Parameter(
     *         name="member",
     *         in="path",
     *         required=true,
     *         description="ID of the member",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="data fetch successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/MemberResource")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to fetch member",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Failed to fetch member. Please try again later."
     *             )
     *         )
     *     )
     * )
     */
    public function getMemberSalesList(Request $request, Member $member)
    {
        $param = $request->all();
        try {
            $data = $this->memberService->getSalesList($member, $param);
            return APIResponse::success(MemberResource::collection($data), 'data fetch successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to fetch member. Please try again later.', 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/member/{id}/points",
     *     operationId="getMemberPointLog",
     *     tags={"Member"},
     *     summary="Get member point log",
     *     description="Returns point log for a specific member",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Member ID",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data fetched successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/MemberResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to fetch member. Please try again later."
     *     )
     * )
     */
    public function getMemberPointLog(Request $request, Member $member)
    {
        $param = $request->all();
        try {
            $data = $this->memberService->getMemberPointLog($member, $param);
            return APIResponse::success(MemberResource::collection($data), 'data fetch successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to fetch member. Please try again later.', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/royal-employee",
     *     operationId="getRoyalEmployee",
     *     tags={"Employees"},
     *     summary="Get list of royal employees",
     *     description="Returns a list of royal employees.",
     *     @OA\Parameter(
     *         name="param",
     *         in="query",
     *         description="Optional parameters for filtering employees.",
     *         required=false,
     *         @OA\Schema(type="object")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/MemberResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to fetch employee",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Failed to fetch employee. Please try again later."
     *             )
     *         )
     *     )
     * )
     */
    public function getRoyalEmployee(Request $request)
    {
        $param = $request->all();
        try {
            $data = $this->memberService->getListOfRoyalEmployees($param);
            return APIResponse::success(MemberResource::collection($data), 'data created successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to fetch employee. Please try again later.', 500);
        }
    }
}
