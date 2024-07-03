<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreItemRequest",
 *     title="StoreItemRequest",
 *     description="Store Item Request",
 *     required={"name", "image", "price", "category_id", "cogs", "qty"},
 *     @OA\Property(property="name", type="string", example="Product Name"),
 *     @OA\Property(property="image", type="string", example="image-url.jpg"),
 *     @OA\Property(property="image_file", type="file", format="binary"),
 *     @OA\Property(property="price", type="integer", example=100),
 *     @OA\Property(property="category_id", type="integer", example=1),
 *     @OA\Property(property="cogs", type="string", example="Cost of goods sold"),
 *     @OA\Property(property="qty", type="integer", example=10),
 * )
 */
class StoreItemRequest extends FormRequest
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
            'name'  => 'required|string|max:86',
            'image'  => 'required|string|max:18',
            'image_file'  => 'file|mimes:jpg,png',
            'price' => 'required|integer',
            'category_id' => 'required|integer',
            "cogs" => 'required',
            "qty" => 'required',
        ];
    }
}
