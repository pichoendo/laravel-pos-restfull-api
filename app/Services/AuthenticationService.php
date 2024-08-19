<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Service class responsible for handling authentication-related operations.
 */
class AuthenticationService
{
    /**
     * Log out the currently authenticated user by deleting their tokens.
     *
     * This method will invalidate all tokens associated with the currently
     * authenticated user, effectively logging them out.
     *
     * @param Request $request The HTTP request object containing user information.
     * @return void
     */
    public function logout(Request $request): void
    {
        $request->user()->tokens()->delete();
    }

    /**
     * Authenticate a user by checking the provided username and password.
     *
     * This method attempts to find an employee by the provided username
     * and then verifies the provided password against the stored hashed password.
     * If authentication is successful, the employee instance is returned; otherwise, null is returned.
     *
     * @param string $username The username of the employee to authenticate.
     * @param string $password The password to verify.
     * @return Employee|null The authenticated employee instance if credentials are correct, otherwise null.
     */
    public function authenticate(string $username, string $password): ?Employee
    {
        $employee = Employee::where('username', $username)->first();

        if (!$employee || !Hash::check($password, $employee->password)) {
            return null;
        }

        return $employee;
    }

    /**
     * Retrieve the currently authenticated user.
     *
     * This method returns the user instance of the currently authenticated user.
     *
     * @return \App\Models\Employee|null The currently authenticated employee instance, or null if no user is authenticated.
     */
    public function getUser()
    {
        return auth()->user();
    }
}
