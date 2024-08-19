<?php

namespace App\Http\Controllers;

use App\Responses\ApiResponse;
use App\Services\AuthenticationService;
use Exception;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * AuthController constructor.
     *
     * This constructor initializes the `CategoryController` by injecting 
     * the `AuthService` and `ApiResponse` dependencies. The `AuthService` 
     * is used for handling authentication-related logic, and the `ApiResponse` 
     * is passed to the parent controller to handle standardized API responses.
     * 
     * @param AuthenticationService $authService  Service to handle authentication logic.
     * @param ApiResponse $apiResponse  Utility to standardize API responses.
     */
    public function __construct(public AuthenticationService $authService, ApiResponse $apiResponse)
    {
        parent::__construct($apiResponse);
    }

    /**
     * Retrieve the authenticated user's information.
     *
     * This method fetches the currently authenticated user's data 
     * using the `authService` and returns it in the response.
     *
     * 
     * @OA\Get(
     *     path="/api/v1/me",
     *     summary="Get user auth data",
     *     description="This endpoint retrieves authentication data for a specific user. Provide the user’s identifier or token to obtain information such as authentication status, user roles, and permissions.",
     *     operationId="Get Auth User Data",
     *     tags={"Authentication"},
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
     *         description="Response when the data fetched successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Fetch successful"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="your-token-here"),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="usernmae", type="string", example="John Doe")
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *          description="Response when the session has expired or the user is not authenticated.",
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
     *             @OA\Property(property="code", type="int", example=422),
     *             @OA\Property(property="message", type="string", example="Oops trouble was happend in our server, please try again in few seconds")
     *         )
     *     ),
     * )
     * 
     * 
     * @param  \Illuminate\Http\Request $request  The request object.
     * @return \Illuminate\Http\Response  The HTTP response containing the user data or error message.
     * 
     */
    public function __invoke(Request $request)
    {
        try {
            $data = $this->authService->getUser();
            return $this->apiResponse->success($data, "Data fetch successful {$request->header('Authorization')}", 200);
        } catch (Exception $e) {
            return $this->apiResponse->error('Failed to fetch user data. Please try again later.', 500, [$e->getMessage()]);
        }
    }
}
