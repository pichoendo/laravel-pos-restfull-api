<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *      title="Update Employee Request",
 *      description="Update Employee request body data",
 *      type="object",
 *      required={"name", "phone_no", "role_id", "address"}
 * )
 */
class UpdateEmployeeRequest extends FormRequest
{
    /**
     * @OA\Property(
     *      title="Name",
     *      description="Name of the employee",
     *      example="John Doe"
     * )
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(
     *      title="Phone Number",
     *      description="Phone number of the employee",
     *      example="1234567890"
     * )
     *
     * @var string
     */
    public $phone_no;

    /**
     * @OA\Property(
     *      title="Role ID",
     *      description="Role ID of the employee",
     *      example=1
     * )
     *
     * @var int
     */
    public $role_id;

    /**
     * @OA\Property(
     *      title="Address",
     *      description="Address of the employee",
     *      example="123 Main St, Anytown, USA"
     * )
     *
     * @var string
     */
    public $address;

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
            'name'  => 'required|string|max:86',
            'phone_no'  => 'required|string|max:18',
            'role_id' => 'required|integer',
            'address' => 'required|string|255',
        ];
    }
}
