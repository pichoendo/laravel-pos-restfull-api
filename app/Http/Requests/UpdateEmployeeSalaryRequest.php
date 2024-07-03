<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use GoldSpecDigital\ObjectOrientedOAS\OpenApi;

class UpdateEmployeeSalaryRequest extends FormRequest
{
    /**
     * @OA\Schema(
     *      schema="UpdateEmployeeSalaryRequest",
     *      type="object",
     *      @OA\Property(property="employee_id", type="string"),
     *      @OA\Property(property="basic_salary", type="string"),
     *      @OA\Property(property="sales_commision", type="string"),
     *      @OA\Property(property="total_salary", type="string"),
     * )
     */

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->role_id == 1;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_id'  => 'required',
            'basic_salary'  => 'required',
            'sales_commision' => 'required',
            'total_salary' => 'required',
        ];
    }
}

