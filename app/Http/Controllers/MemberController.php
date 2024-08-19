<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Http\Resources\MemberResource;
use App\Responses\ApiResponse;
use App\Services\MemberService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class MemberController extends PaginateController implements HasMiddleware
{
    /**
     * MemberController constructor.
     *
     * 
     * This constructor initializes the `MemberController` by injecting 
     * the `MemberService` and `ApiResponse` dependencies. The `MemberService` 
     * is used for handling Member CRUD logic, and the `ApiResponse` 
     * is passed to the parent controller to handle standardized API responses.
     *
     *  
     * @param ItemService $itemService
     * @param  \App\Http\Responses\ApiResponse  $apiResponse  The service for standardized API responses.
     */
    public function __construct(public MemberService $memberService, ApiResponse $apiResponse)
    {
        parent::__construct($apiResponse);
    }

    /**
     * Define the middleware applied to the controller methods.
     *
     * This method returns an array of middleware configurations:
     * - The 'manage_member' permission is required to access the 'update', 'store', and 'destroy' methods.
     * - The 'manage_member' or 'consume_member' permissions are required to access the 'index', 'show', and 'getLoyalMember' methods.
     *
     * @return array The array of middleware applied to the controller methods.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:manage_member', only: ['update',  'store', 'destroy']),
            new Middleware('permission:consume_member|manage_member', only: ['index', 'show', 'getLoyalMember']),
        ];
    }

    /**
     * Display a listing of the members.
     *
     * 
     * @OA\Get(
     *     path="/api/v1/member",
     *     summary="Mengambil list data member",
     *     description="This endpoint is used to retrieve a paginated list of members. You can set the number of items per page using the 'perPage' parameter and specify the page using the 'page' parameter. It is also possible to filter the results by keyword using the 'search' parameter",
     *     operationId="GetMemberList",
     *     tags={"Member"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer token for authentication",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer <your-token-here>"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response when the list of members is successfully retrieved",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data member fetched successfully"),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", example="1"),
     *                         @OA\Property(property="code", type="string", example="EMP/2020"),
     *                         @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                         @OA\Property(property="name", type="string", example="Member 1"),
     *                         @OA\Property(property="email", type="string", example="email@email.com"),
     *                         @OA\Property(property="phone_no", type="float", example="08789387748"),
     *                     ),
     *                 ),
     *                 @OA\Property(property="paginate", type="object",
     *                     @OA\Property(property="current_page", type="int", example="1"),
     *                     @OA\Property(property="per_page", type="int", example="10"),
     *                     @OA\Property(property="total", type="int", example="20")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Response when the session has expired or the user is not authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=401),
     *             @OA\Property(property="message", type="string", example="Your session has expired or you are not authenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Response when there are issues on the server",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=500),
     *             @OA\Property(property="message", type="string", example="Oops, something went wrong on our server. Please try again later.")
     *         )
     *     )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('page', $this->PER_PAGE);
            $page = $request->input('page', $this->START_PAGE);
            $search = $request->input('search', "");

            $data = $this->memberService->getData($search, $page, $perPage);
            return $this->apiResponse->success(MemberResource::collection($data), 'Fetch successfully', 200);
        } catch (Exception $ex) {
            return $this->apiResponse->error('Failed to create category. Please try again later.', 500);
        }
    }

    /**
     * Store a newly created item in storage.
     *
     * @OA\Post(
     *     path="/api/v1/member",
     *     summary="Save a member",
     *     description="The endpoint for create a member.",
     *     operationId="SaveMember",
     *     tags={"Member"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer token for authentication",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer <your-token-here>"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *              required={"name","email","phone_no"},
     *              @OA\Property(property="name", type="string", example="Member 1"),
     *              @OA\Property(property="email", type="string", example="email@email.com"),
     *              @OA\Property(property="phone_no", type="string", example="08789387748"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response when the member is successfully created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data member saved successfully"),
     *             @OA\Property(property="result", type="object",
     *                  @OA\Property(property="id", type="string", example="1"),
     *                  @OA\Property(property="code", type="string", example="EMP/2020"),
     *                  @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                  @OA\Property(property="name", type="string", example="Member 1"),
     *                  @OA\Property(property="email", type="string", example="email@email.com"),
     *                  @OA\Property(property="phone_no", type="float", example="08789387748"),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Response when the session has expired or the user is not authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=401),
     *             @OA\Property(property="message", type="string", example="Your session has expired or you are not authenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Response when there are issues on the validation process",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=422),
     *             @OA\Property(property="message", type="string", example="Form validation unsuccessful. Check the error details for futhermore"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="field", type="array",
     *                     @OA\Items(type="string", example="The field is required.")
     *                 ),
     *                 @OA\Property(property="anotherField", type="array",
     *                     @OA\Items(type="string", example="The field must be a string.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Response when there are issues on the server",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=500),
     *             @OA\Property(property="message", type="string", example="Oops, something went wrong on our server. Please try again later.")
     *         )
     *     )
     * )
     * 
     * @param StoreMemberRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreMemberRequest $request)
    {

        $param = $request->validated();
        try {
            $data = $this->memberService->create($param);
            return $this->apiResponse->success(new MemberResource($data), 'Member created successfully ', 201);
        } catch (Exception $ex) {
            return $this->apiResponse->error($ex->getMessage(), 500);
        }
    }

    /**
     * Display the specified item.
     *
     * @OA\Get(
     *     path="/api/v1/member/{uuid}",
     *     summary="Retrieve a member by its UUID.",
     *     description="This endpoint allows you to retrieve details of a specific member using its UUID. Provide the UUID of the member in the request to get information such as the member's name, description, and any other relevant details",
     *     operationId="GetMember",
     *     tags={"Member"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer token for authentication",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer <your-token-here>"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the data",
     *         @OA\Schema(
     *             type="string",
     *             example="Skfr-4584kndir4-456"
     *         )
     *     ), 
     *     @OA\Response(
     *         response=200,
     *         description="Response when the data retrived successfully",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data member fetched successfully"),
     *             @OA\Property(property="result", type="object",
     *                  @OA\Property(property="id", type="string", example="1"),
     *                  @OA\Property(property="code", type="string", example="EMP/2020"),
     *                  @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                  @OA\Property(property="name", type="string", example="Member 1"),
     *                  @OA\Property(property="email", type="string", example="email@email.com"),
     *                  @OA\Property(property="phone_no", type="float", example="08789387748"),
     *             ),
     *                  )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Response when the session has expired or the user is not authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=401),
     *             @OA\Property(property="message", type="string", example="Your session has expired or you are not authenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Response when the data was not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=404),
     *             @OA\Property(property="message", type="string", example="The data was not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Response when there are issues on the server",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=500),
     *             @OA\Property(property="message", type="string", example="Oops, something went wrong on our server. Please try again later.")
     *         )
     *     )
     * )
     * 
     * @param Item $item
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Member $Member)
    {
        return $this->apiResponse->success(new MemberResource($Member), 'Member fetched successfully', 200);
    }

    /**
     * Update the specified item in storage.
     *
     * @OA\Put(
     *     path="/api/v1/member/{uuid}",
     *     summary="Update a member",
     *     description="This endpoint allows you to update the details of an existing member. Provide the member's UUID and the updated information in the request body to modify attributes.",
     *     operationId="UpdateMember",
     *     tags={"Member"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer token for authentication",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer <your-token-here>"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the data",
     *         @OA\Schema(
     *             type="string",
     *             example="Skfr-4584kndir4-456"
     *         )
     *     ),
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(
     *              @OA\Property(property="name", type="string", example="Member 1"),
     *              @OA\Property(property="email", type="string", example="email@email.com"),
     *              @OA\Property(property="phone_no", type="float", example="08789387748"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Response when the session has expired or the user is not authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=401),
     *             @OA\Property(property="message", type="string", example="Your session has expired or you are not authenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response when the data updated successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data member updated successfully"),
     *             @OA\Property(property="result", type="object",
     *                  @OA\Property(property="id", type="string", example="1"),
     *                  @OA\Property(property="code", type="string", example="EMP/2020"),
     *                  @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                  @OA\Property(property="name", type="string", example="Member 1"),
     *                  @OA\Property(property="email", type="string", example="email@email.com"),
     *                  @OA\Property(property="phone_no", type="float", example="08789387748"),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Response when the data was not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=404),
     *             @OA\Property(property="message", type="string", example="The data was not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Response when there are issues on the validation process",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=422),
     *             @OA\Property(property="message", type="string", example="Form validation unsuccessful. Check the error details for futhermore"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="field", type="array",
     *                     @OA\Items(type="string", example="The field is required.")
     *                 ),
     *                 @OA\Property(property="anotherField", type="array",
     *                     @OA\Items(type="string", example="The field must be a string.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Response when there are issues on the server",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=500),
     *             @OA\Property(property="message", type="string", example="Oops, something went wrong on our server. Please try again later.")
     *         )
     *     )
     * )
     * 
     * 
     * @param UpdateMemberRequest $request
     * @param IMember $Member
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateMemberRequest $request, Member $member)
    {
        $param = $request->validated();
        try {
            $data = $this->memberService->update($member, $param);
            return $this->apiResponse->success(new MemberResource($data), 'Member updated successfully', 201);
        } catch (Exception $ex) {
            return $this->apiResponse->error($ex->getMessage(), 500);
        }
    }
    /**
     * Remove the specified item.
     *
     * 
     * @OA\Delete(
     *     path="/api/v1/member/{uuid}",
     *     summary="Delete a member",
     *     description="This endpoint allows you to delete a specific member from the system. Provide the UUID of the member you wish to remove, and the member will be permanently deleted from the database",
     *     operationId="DeleteMember",
     *     tags={"Member"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer token for authentication",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer <your-token-here>"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the data",
     *         @OA\Schema(
     *             type="string",
     *             example="Skfr-4584kndir4-456"
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Response when the session has expired or the user is not authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=401),
     *             @OA\Property(property="message", type="string", example="Your session has expired or you are not authenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Response when the data was not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=404),
     *             @OA\Property(property="message", type="string", example="The data was not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response when the data deleted successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data member deleted successfully"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Response when there are issues on the server",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=500),
     *             @OA\Property(property="message", type="string", example="Oops, something went wrong on our server. Please try again later.")
     *         )
     *     )
     * )
     * 
     * 
     * @param Member $member
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Member $member)
    {
        try {
            $this->memberService->destroy($member);
            return $this->apiResponse->success(null, 'Member deleted successfully', 201);
        } catch (Exception $ex) {
            return $this->apiResponse->error($ex->getMessage(), 500);
        }
    }

    /**
     * Display a listing of the member sales.
     *
     * @OA\Get(
     *     path="/api/v1/employe/{uuid}/sales",
     *     summary="Retrive a list selected member managed sales. ",
     *     description="This endpoint is used to retrieve a paginated list of member managed sales. You can set the number of items per page using the 'perPage' parameter and specify the page using the 'page' parameter. It is also possible to filter the results by keyword using the 'search' parameter",
     *     operationId="GetMemberSalesList",
     *     tags={"Member"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer token for authentication",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer <your-token-here>"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response when the list of member managed sales is successfully retrieved",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data member fetched successfully"),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                       
     *                     ),
     *                 ),
     *                 @OA\Property(property="paginate", type="object",
     *                     @OA\Property(property="current_page", type="int", example="1"),
     *                     @OA\Property(property="per_page", type="int", example="10"),
     *                     @OA\Property(property="total", type="int", example="20")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Response when the session has expired or the user is not authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=401),
     *             @OA\Property(property="message", type="string", example="Your session has expired or you are not authenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Response when there are issues on the server",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=500),
     *             @OA\Property(property="message", type="string", example="Oops, something went wrong on our server. Please try again later.")
     *         )
     *     )
     * )
     * 
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMemberSalesList(Request $request, Member $member)
    {

        try {
            $perPage = $request->input('per_page', $this->PER_PAGE);
            $page = $request->input('page', $this->START_PAGE);
            $dateRange = $request->input('dateRange', null);

            $data = $this->memberService->getSalesList($member, $page, $perPage, $dateRange);
            return $this->apiResponse->success(MemberResource::collection($data), 'data fetch successfully', 200);
        } catch (Exception $ex) {
            return $this->apiResponse->error('Failed to fetch member. Please try again later.', 500);
        }
    }
    /**
     * Display a listing of the member point logs.
     *
     * @OA\Get(
     *     path="/api/v1/employe/{uuid}/point",
     *     summary="Retrive a list selected member points",
     *     description="This endpoint is used to retrieve a paginated list of member points. You can set the number of items per page using the 'perPage' parameter and specify the page using the 'page' parameter. It is also possible to filter the results by keyword using the 'search' parameter",
     *     operationId="GetMemberCommissionList",
     *     tags={"Member"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer token for authentication",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer <your-token-here>"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response when the list of member points is successfully retrieved",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data member fetched successfully"),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", example="1"),
     *                         @OA\Property(property="code", type="string", example="COM/2020"),
     *                         @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                         @OA\Property(property="name", type="string", example="Member 1"),
     *                         @OA\Property(property="type", type="string", example="add"),
     *                         @OA\Property(property="value", type="float", example="200"),
     *                     ),
     *                 ),
     *                 @OA\Property(property="paginate", type="object",
     *                     @OA\Property(property="current_page", type="int", example="1"),
     *                     @OA\Property(property="per_page", type="int", example="10"),
     *                     @OA\Property(property="total", type="int", example="20")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Response when the session has expired or the user is not authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=401),
     *             @OA\Property(property="message", type="string", example="Your session has expired or you are not authenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Response when there are issues on the server",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=500),
     *             @OA\Property(property="message", type="string", example="Oops, something went wrong on our server. Please try again later.")
     *         )
     *     )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMemberPointLog(Request $request, Member $member)
    {

        try {
            $perPage = $request->input('per_page', $this->PER_PAGE);
            $page = $request->input('page', $this->START_PAGE);
            $dateRange = $request->input('dateRange', null);

            $data = $this->memberService->getMemberPointLog($member, $page, $perPage, $dateRange);
            return $this->apiResponse->success(MemberResource::collection($data), 'data fetch successfully', 200);
        } catch (Exception $ex) {
            return $this->apiResponse->error('Failed to fetch member. Please try again later.', 500);
        }
    }

    /**
     * Display a listing of the loyal member.
     *
     * @OA\Get(
     *     path="/api/v1/member/loyal",
     *     summary="Retrive a loyal member",
     *     description="This endpoint is used to retrieve a paginated list of loyal member. You can set the number of items per page using the 'perPage' parameter and specify the page using the 'page' parameter. It is also possible to filter the results by keyword using the 'search' parameter",
     *     operationId="GetMemberLoyalList",
     *     tags={"Member"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer token for authentication",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer <your-token-here>"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response when the list of members is successfully retrieved",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data member fetched successfully"),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", example="1"),
     *                         @OA\Property(property="code", type="string", example="EMP/2020"),
     *                         @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                         @OA\Property(property="name", type="string", example="Member 1"),
     *                         @OA\Property(property="email", type="string", example="email@email.com"),
     *                         @OA\Property(property="phone_no", type="float", example="08789387748"),
     *                     ),
     *                 ),
     *                 @OA\Property(property="paginate", type="object",
     *                     @OA\Property(property="current_page", type="int", example="1"),
     *                     @OA\Property(property="per_page", type="int", example="10"),
     *                     @OA\Property(property="total", type="int", example="20")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Response when the session has expired or the user is not authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=401),
     *             @OA\Property(property="message", type="string", example="Your session has expired or you are not authenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Response when there are issues on the server",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=500),
     *             @OA\Property(property="message", type="string", example="Oops, something went wrong on our server. Please try again later.")
     *         )
     *     )
     * )
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLoyalMember(Request $request)
    {

        try {
            $perPage = $request->input('per_page', $this->PER_PAGE);
            $page = $request->input('page', $this->START_PAGE);
            $dateRange = $request->input('dateRange', null);

            $data = $this->memberService->getListOfLoyalMember($page, $perPage, $dateRange);
            return $this->apiResponse->success(MemberResource::collection($data), 'data created successfully', 200);
        } catch (Exception $ex) {
            return $this->apiResponse->error('Failed to fetch member. Please try again later.', 500);
        }
    }
}
