<?php

namespace Tests\Unit\Models;

use App\Models\{Product, ProductVariant};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_has_many_variants(): void
    {
        $product = Product::factory()->create();
        $v1 = ProductVariant::factory()->for($product)->create();
        $v2 = ProductVariant::factory()->for($product)->create();

        $this->assertCount(2, $product->variants()->get());
        $this->assertTrue($product->variants->contains($v1));
        $this->assertTrue($product->variants->contains($v2));
    }
}
