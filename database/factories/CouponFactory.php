<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->numberBetween(1000, 9999),
            'value' => $this->faker->numberBetween(1000, 9999),
            'uuid' => (string) Str::uuid(), // Generate a UUID
            'name' => $this->faker->name,
        ];
    }
}
