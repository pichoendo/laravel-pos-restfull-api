<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


/**
 * @OA\Schema(
 *     schema="UpdateCategoryRequest",
 *     type="object",
 *     title="Update Category Request",
 *     description="Update Category Request model",
 *     required={"name", "images"},
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the category"
 *     ),
 *     @OA\Property(
 *         property="images",
 *         type="string",
 *         description="Images of the category"
 *     )
 * )
 */
class UpdateCategoryRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request. only supervisior and admin who can do this
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
            'name' => 'required',
            'images' => 'required',
        ];
    }
}
