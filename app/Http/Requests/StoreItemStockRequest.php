<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreItemStockRequest",
 *     title="StoreItemStockRequest",
 *     description="Store Item Stock Request",
 *     required={"cogs", "qty"},
 *     @OA\Property(property="cogs", type="string", example="Cost of goods sold"),
 *     @OA\Property(property="qty", type="integer", example=10),
 * )
 */
class StoreItemStockRequest extends FormRequest
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
            'cogs'  => 'required',
            'qty'  => 'required',
        ];
    }
}
