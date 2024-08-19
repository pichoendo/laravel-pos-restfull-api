<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
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
            'basic_salary' => $this->faker->randomFloat(2, 0, 1),
            'commission_percentage' => $this->faker->numberBetween(1, 100),
        ];
    }
}
