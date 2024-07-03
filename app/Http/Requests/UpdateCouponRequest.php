<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateCouponRequest",
 *     title="UpdateCouponRequest",
 *     description="Update Coupon Request",
 *     required={"name", "code", "value"},
 *     @OA\Property(property="name", type="string", example="Summer Sale"),
 *     @OA\Property(property="code", type="string", example="SUMMER20"),
 *     @OA\Property(property="value", type="decimal", example=20.5),
 * )
 */
class UpdateCouponRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return in_array(auth()->user()->role_id, [1, 2]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'  => 'required',
            'code'  => 'required',
            'value' => 'required',
        ];
    }
}
