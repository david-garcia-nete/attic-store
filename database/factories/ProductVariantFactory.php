<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => strtoupper($this->faker->unique()->bothify('SKU-####')),
            'option_summary' => $this->faker->optional()->words(2, true),
            'price' => $this->faker->optional(0.6)->randomFloat(2, 1, 500),
            'weight_oz' => $this->faker->optional()->randomFloat(2, 1, 64),
            'length_in' => $this->faker->optional()->randomFloat(2, 1, 30),
            'width_in' => $this->faker->optional()->randomFloat(2, 1, 30),
            'height_in' => $this->faker->optional()->randomFloat(2, 1, 30),
            'is_active' => true,
        ];
    }
}
