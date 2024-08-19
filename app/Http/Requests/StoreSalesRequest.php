<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('manage_sales');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'purchased_by'     => 'string|max:86',
            'tax'           => '',
            'discount'      => '',
            'card_no'       => '',
            'coupon_id'     => '',
            'status'        => 'required',
            'sub_total'     => 'required',
            'total'         => 'required',
            'coupon'        => '',
            'use_point'     => '',
            "cart.*.item_id"    => "",
            "cart.*.qty"        => "",
            "cart.*.price"      => "",
            "cart.*.sub_total"  => ""
        ];
    }
}
