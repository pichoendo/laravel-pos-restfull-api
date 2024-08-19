<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Responses\ApiResponse;
use App\Services\AuthenticationService;
use Exception;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /**
     * LoginController constructor.
     *
     * This constructor initializes the `LoginController` by injecting 
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
     * Handle the single action to login.
     * 
     * @OA\Post(
     *     path="/api/v1/login",
     *     summary="Employee login",
     *     description="Log in as an employee and generate a access token",
     *     operationId="login",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username","password"},
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response when login is successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="your-token-here"),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="usernmae", type="string", example="John Doe")
     *             ),
     *             @OA\Property(property="message", type="string", example="Login successful")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Response when the credentials are invalid",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=401),
     *             @OA\Property(property="message", type="string", example="Invalid Credentials")
     *         )
     *     ),
     *    @OA\Response(
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
     * )
     *
     * 
     * @param  \App\Http\Requests\LoginRequest  $request
     * @return ApiResponse
     * 
     */
    public function __invoke(LoginRequest $request)
    {
        try {

            $credentials = $request->validated();
            $employee = $this->authService->authenticate($credentials['username'], $credentials['password']);

            if (!$employee)
                return $this->apiResponse->error('Invalid credentials', 401);

            $token = $employee->createToken('authOfPost')->plainTextToken;

            return $this->apiResponse->success([
                'token' => $token,
                'user' => $employee->only(['id', 'uuid', 'name', 'email', 'role_id']),
            ], 'Login successful', 201);
        } catch (Exception $e) {
            return $this->apiResponse->error('Login failed. Please try again later.', 500, [$e->getMessage()]);
        }
    }
}
