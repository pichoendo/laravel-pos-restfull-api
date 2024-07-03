<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     title="UpdateItemStockRequest",
 *     description="Update Item Stock Request Schema",
 *     type="object",
 *     required={"item_id", "cogs", "qty"}
 * )
 */
class UpdateItemStockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return in_array(auth()->user()->role_id, [1, 2]);
    }

    /**
     * @OA\Property(
     *     property="item_id",
     *     description="Item ID (required)",
     *     type="integer"
     * )
     * @OA\Property(
     *     property="cogs",
     *     description="Cost of Goods Sold (required)",
     *     type="string"
     * )
     * @OA\Property(
     *     property="qty",
     *     description="Quantity (required)",
     *     type="integer"
     * )
     *
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'item_id'  => 'required|integer',
            'cogs'     => 'required|string',
            'qty'      => 'required|integer',
        ];
    }
}
