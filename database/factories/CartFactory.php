<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CartFactory extends Factory
{
    protected $model = Cart::class;

    public function definition(): array
    {
        return [
            'user_id' => $this->faker->optional()->boolean(70) ? User::factory() : null,
            'session_id' => Str::uuid()->toString(),
        ];
    }
}
