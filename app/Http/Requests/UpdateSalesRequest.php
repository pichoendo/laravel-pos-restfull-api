<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalesRequest extends FormRequest
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
            'member_id'  => 'string|max:86',
            'employee_id'  => 'string|max:86',
            'tax'  => '',
            'discount'  => '',
            'card_no' => '',
            'coupon_id' => '',
            'status' => 'required',
            'sub_total'  => 'required',
            'total'  => 'required',
            'coupon'  => '',
            'use_point'  => '',
            "cart.*.item_id" => "required",
            "cart.*.qty" => "required",
            "cart.*.price" => "required",
            "cart.*.sub_total" => "required"
        ];
    }
}
