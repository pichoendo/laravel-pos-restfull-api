<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
/**
 * @OA\Schema(
 *     schema="StoreRoleRequest",
 *     required={"name", "basic_salary", "commission_percentage"},
 *     @OA\Property(property="name", type="string", maxLength=86),
 *     @OA\Property(property="basic_salary", type="string"),
 *     @OA\Property(property="commission_percentage", type="string")
 * )
 */
class StoreRoleRequest extends FormRequest
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
            'basic_salary'  => 'required',
            'commission_percentage'  => 'required',
        ];
    }
}
