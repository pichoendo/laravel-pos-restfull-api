<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(), // Generate a UUID
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone_no' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'role_id' => $this->faker->numberBetween(1, 10),
            'username' => $this->faker->unique()->userName, // Generate a unique username
            'password' => Hash::make('password'), // Default password, hashed
        ];
    }

    public function withRole($roleId)
    {
        return $this->state([
            'role_id' => $roleId,
        ]);
    }
}
