<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);
        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->numerify('####'),
            'price' => $this->faker->randomFloat(2, 1, 500),
            'cost' => $this->faker->randomFloat(2, 0.5, 400),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
