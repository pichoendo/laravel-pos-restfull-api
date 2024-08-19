<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Items>
 */
class ItemFactory extends Factory
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
            'price' => $this->faker->numberBetween(1, 100) * 1000,
            'category_id' => $this->faker->numberBetween(1, 100),
        ];
    }

    public function withCategory($categories)
    {
        return $this->state([
            'category_id' => $categories[rand(0, sizeof($categories) - 1)]->id,
        ]);
    }
}
