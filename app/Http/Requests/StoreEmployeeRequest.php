<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreEmployeeRequest",
 *     title="StoreEmployeeRequest",
 *     description="Store Employee Request",
 *     required={"name", "phone_no", "role_id", "address", "username", "password", "password_confirmation"},
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="phone_no", type="string", example="1234567890"),
 *     @OA\Property(property="role_id", type="integer", example=1),
 *     @OA\Property(property="address", type="string", example="123 Main St, City"),
 *     @OA\Property(property="username", type="string", example="johndoe"),
 *     @OA\Property(property="email", type="string", example="johndoe@gmail.com"),
 *     @OA\Property(property="password", type="string", format="password", example="secret"),
 *     @OA\Property(property="password_confirmation", type="string", format="password", example="secret"),
 * )
 */
class StoreEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->role_id == 1;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'  => 'required|string|max:86',
            'phone_no'  => 'required|string|max:18',
            'role_id' => 'required|integer',
            'address' => 'required|string|255',
            'email' => 'required|unique:employees,email',
            'username' => 'required|string|max:10',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password',
        ];
    }
}
