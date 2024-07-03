<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     title="StoreSalesRequest",
 *     description="Store Sales Request Schema",
 *     type="object",
 *     required={"status", "sub_total", "total", "cart"}
 * )
 */
class StoreSalesRequest extends FormRequest
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
     * @OA\Property(
     *     property="member_id",
     *     description="Member ID (optional)",
     *     type="string",
     *     maxLength=86
     * )
     * @OA\Property(
     *     property="employee_id",
     *     description="Employee ID (optional)",
     *     type="string",
     *     maxLength=86
     * )
     * @OA\Property(
     *     property="tax",
     *     description="Tax amount",
     *     type="string"
     * )
     * @OA\Property(
     *     property="discount",
     *     description="Discount amount",
     *     type="string"
     * )
     * @OA\Property(
     *     property="card_no",
     *     description="Card number (optional)",
     *     type="string"
     * )
     * @OA\Property(
     *     property="coupon_id",
     *     description="Coupon ID (optional)",
     *     type="string"
     * )
     * @OA\Property(
     *     property="status",
     *     description="Status of the sales (required)",
     *     type="string"
     * )
     * @OA\Property(
     *     property="sub_total",
     *     description="Subtotal amount (required)",
     *     type="string"
     * )
     * @OA\Property(
     *     property="total",
     *     description="Total amount (required)",
     *     type="string"
     * )
     * @OA\Property(
     *     property="coupon",
     *     description="Coupon (optional)",
     *     type="string"
     * )
     * @OA\Property(
     *     property="use_point",
     *     description="Use point (optional)",
     *     type="string"
     * )
     * @OA\Property(
     *     property="cart",
     *     description="Cart items (required)",
     *     type="array",
     *     @OA\Items(
     *         type="object",
     *         @OA\Property(
     *             property="item_id",
     *             description="Item ID",
     *             type="string"
     *         ),
     *         @OA\Property(
     *             property="qty",
     *             description="Quantity",
     *             type="string"
     *         ),
     *         @OA\Property(
     *             property="price",
     *             description="Price",
     *             type="string"
     *         ),
     *         @OA\Property(
     *             property="sub_total",
     *             description="Subtotal for the item",
     *             type="string"
     *         ),
     *     )
     * )
     *
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'member_id'     => 'string|max:86',
            'employee_id'   => 'string|max:86',
            'tax'           => '',
            'discount'      => '',
            'card_no'       => '',
            'coupon_id'     => '',
            'status'        => 'required',
            'sub_total'     => 'required',
            'total'         => 'required',
            'coupon'        => '',
            'use_point'     => '',
            "cart.*.item_id"    => "required",
            "cart.*.qty"        => "required",
            "cart.*.price"      => "required",
            "cart.*.sub_total"  => "required"
        ];
    }
}
