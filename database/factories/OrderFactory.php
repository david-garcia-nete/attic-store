<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 0, 999);
        $discount = $this->faker->randomFloat(2, 0, min(100, $subtotal));
        $shipping = $this->faker->randomFloat(2, 0, 25);
        $tax = round(($subtotal - $discount) * 0.08, 2);
        $grand = max(0, $subtotal - $discount + $shipping + $tax);

        return [
            'number' => strtoupper(Str::random(3)).'-'.$this->faker->unique()->numerify('####'),
            'user_id' => $this->faker->optional()->boolean(70) ? User::factory() : null,
            'subtotal' => $subtotal,
            'discount_total' => $discount,
            'shipping_total' => $shipping,
            'tax_total' => $tax,
            'grand_total' => $grand,
            'status' => 'pending',
        ];
    }
}
