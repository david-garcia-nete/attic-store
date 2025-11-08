<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryFactory extends Factory
{
    protected $model = Inventory::class;

    public function definition(): array
    {
        $onHand = $this->faker->numberBetween(0, 200);
        $reserved = $this->faker->numberBetween(0, $onHand);
        return [
            'product_variant_id' => ProductVariant::factory(),
            'qty_on_hand' => $onHand,
            'qty_reserved' => $reserved,
            'bin_location' => strtoupper($this->faker->bothify('A##-B##')),
        ];
    }
}
