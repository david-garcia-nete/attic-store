<?php

namespace Tests\Unit\Models;

use App\Models\{Inventory, Product, ProductVariant};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantTest extends TestCase
{
    use RefreshDatabase;

    public function test_variant_belongs_to_product_and_has_one_inventory(): void
    {
        $product = Product::factory()->create(['price' => 20.00]);
        $variant = ProductVariant::factory()->for($product)->create(['price' => 25.00]);
        $inventory = Inventory::factory()->create(['product_variant_id' => $variant->id]);

        $this->assertEquals($product->id, $variant->product->id);
        $this->assertEquals($inventory->id, $variant->inventory->id);
    }

    public function test_price_effective_falls_back_to_product_price(): void
    {
        $product = Product::factory()->create(['price' => 9.99]);
        $v1 = ProductVariant::factory()->for($product)->create(['price' => null]);
        $v2 = ProductVariant::factory()->for($product)->create(['price' => 12.34]);

        $this->assertEquals(9.99, $v1->priceEffective());
        $this->assertEquals(12.34, $v2->priceEffective());
    }
}
