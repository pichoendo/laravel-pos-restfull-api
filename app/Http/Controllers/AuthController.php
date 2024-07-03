<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Responses\APIResponse;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Employee login",
     *     description="Log in an employee and generate a token",
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
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="your-token-here"),
     *                 @OA\Property(property="name", type="string", example="John Doe")
     *             ),
     *             @OA\Property(property="message", type="string", example="Login successful")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Login failed. Please try again later.",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Login failed. Please try again later.")
     *         )
     *     )
     * )

     *
     * @param  \App\Http\Requests\LoginRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        try {
            // Validate incoming login request
            $request->validated();

            // Attempt to find the employee by username
            $employee = Employee::where('username', $request->username)->first();

            // Check if employee exists and verify passwords
            if (!$employee || !Hash::check($request->password, $employee->password)) {
                return APIResponse::error('Invalid credentials', 401);
            }

            // Generate a new plain text token for the employee
            $token = $employee->createToken('token-name')->plainTextToken;

            // Prepare success response with token and employee name
            $success['token'] = $token;
            $success['name'] =  $employee->name;

            // Return success response with token and name
            return APIResponse::success($success, 'Login successful', 200);
        } catch (\Exception $e) {
            // Handle exceptions gracefully, e.g., log them
            return APIResponse::error('Login failed. Please try again later.', 500);
        }
    }
}
