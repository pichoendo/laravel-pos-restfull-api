<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
            'address' => 'required|string|max:255',
            'email' => 'required|unique:employees,email',
            'username' => 'required|string|max:10',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password',
        ];
    }
}
