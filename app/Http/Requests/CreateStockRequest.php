<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
* @OA\Schema(
*   schema="CreateStockRequest",
*   title="Create Stock Request",
*   required={"item_id", "cogs"},
*   @OA\Property(
*      property="item_id",
*      type="string"
*   ),
*   @OA\Property(
*      property="cogs",
*      type="string"
*   )
* )
*/
class CreateStockRequest extends FormRequest
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
            'item_id' => 'required',
            'cogs' => 'required'
        ];
    }
}
