<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     title="StoreMemberRequest",
 *     description="Store member request body data",
 *     type="object",
 *     required={"name", "phone_no"}
 * )
 */
class StoreMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return in_array(auth()->user()->role_id, [1, 3]);
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
            'phone_no'  => 'required|unique:members,phone_no,NULL,id,deleted_at,NULL',
        ];
    }

    /**
     * @OA\Property(
     *     property="name",
     *     type="string",
     *     description="Name of the member",
     *     example="John Doe"
     * )
     *
     * @OA\Property(
     *     property="phone_no",
     *     type="string",
     *     description="Phone number of the member",
     *     example="+123456789"
     * )
     */
}
