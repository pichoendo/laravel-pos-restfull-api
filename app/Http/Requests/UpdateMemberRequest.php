<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateMemberRequest",
 *     title="Update Member Request",
 *     required={"name", "phone_no"},
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         maxLength=86,
 *         description="Member's name"
 *     ),
 *     @OA\Property(
 *         property="phone_no",
 *         type="string",
 *         description="Member's phone number"
 *     ),  
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         description="Member's email"
 *    )
 * )
 */

class UpdateMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
            'email' => 'required|unique:members,email',
            'phone_no'  => 'required|unique:members,phone_no',
        ];
    }
}
