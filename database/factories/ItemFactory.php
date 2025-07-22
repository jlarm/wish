<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
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
            'name' => $this->faker->words(3, true),
            'size' => $this->faker->optional()->randomElement(['XS', 'S', 'M', 'L', 'XL', 'One Size']),
            'color' => $this->faker->optional()->colorName(),
            'link' => $this->faker->optional()->url(),
            'price' => $this->faker->optional()->randomFloat(2, 10, 500),
            'store' => $this->faker->optional()->company(),
            'purchased' => $this->faker->boolean(20),
            'purchased_by' => null,
            'purchased_date' => null,
            'delivered' => $this->faker->boolean(10),
            'delivered_date' => null,
            'hidden' => false,
        ];
    }
}
