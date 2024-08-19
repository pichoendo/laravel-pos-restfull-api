<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ItemStock>
 */
class ItemStockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_id' => $this->faker->name,
            'cogs' => $this->faker->randomFloat(2, 10000, 50000),
            'qty' => $this->faker->numberBetween(10, 100),
        ];
    }

    public function withItem($itemId)
    {
        return $this->state([
            'item_id' => $itemId,
        ]);
    }
}
