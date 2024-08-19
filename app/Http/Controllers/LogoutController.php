<?php

namespace App\Http\Controllers;

use App\Responses\ApiResponse;
use App\Services\AuthenticationService;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    /**
     * LogoutController constructor.
     *
     * This constructor initializes the `LogoutController` by injecting 
     * the `AuthenticationService` and `ApiResponse` dependencies. The `AuthenticationService` 
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
     * Handle the single action to logout.
     *
     * 
     * @OA\Put(
     *     path="/api/v1/logout",
     *     summary="Logout",
     *     description="This endpoint allows you to log out of the system. It invalidates the current session and ensures that the user is no longer authenticated. No additional information is required in the request body.",
     *     operationId="Logout",
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
     *         response=200,
     *         description="Response when the user logout successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logout successfully"),
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
     * @param  \Illuminate\Http\Request  $request
     * @return ApiResponse $apiResponse  Utility to standardize API responses.
     */
    public function __invoke(Request $request)
    {
        try {
            $this->authService->logout($request);
            return $this->apiResponse->success([], 'logout successful', 201);
        } catch (\Exception $e) {
            return $this->apiResponse->error("Logout error", 400, [$e->getMessage()]);
        }
    }
}
