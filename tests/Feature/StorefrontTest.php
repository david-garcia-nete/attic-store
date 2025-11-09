<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_lists_active_products(): void
    {
        $active = Product::factory()->count(3)->create();
        $inactive = Product::factory()->create(['is_active' => false]);
        // attach variants so eager load isn't empty
        foreach ($active as $p) {
            ProductVariant::factory()->for($p)->create();
        }

        $resp = $this->get(route('home'));
        $resp->assertOk();
        foreach ($active as $p) {
            $resp->assertSee($p->name);
        }
        $resp->assertDontSee($inactive->name);
    }

    public function test_index_applies_search_filter(): void
    {
        $match = Product::factory()->create(['name' => 'Red Wool Hat']);
        ProductVariant::factory()->for($match)->create();
        $noMatch = Product::factory()->create(['name' => 'Blue Cotton Scarf']);
        ProductVariant::factory()->for($noMatch)->create();

        $resp = $this->get(route('products.index', ['q' => 'Wool']));
        $resp->assertOk();
        $resp->assertSee('Red Wool Hat');
        $resp->assertDontSee('Blue Cotton Scarf');
    }

    public function test_product_show_page_displays_product(): void
    {
        $product = Product::factory()->create(['slug' => 'my-slug', 'is_active' => true]);
        ProductVariant::factory()->for($product)->create();

        $resp = $this->get(route('products.show', ['slug' => 'my-slug']));
        $resp->assertOk();
        $resp->assertSee($product->name);
    }
}
