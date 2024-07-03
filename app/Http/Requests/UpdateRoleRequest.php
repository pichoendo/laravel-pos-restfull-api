<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** @OA\Schema(
 *      title="Update Role Request",
 *      description="Update Role request body data",
 *      type="object",
 *      required={"name", "basic_salary", "commission_percentage"}
 * )
 */
class UpdateRoleRequest extends FormRequest

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
