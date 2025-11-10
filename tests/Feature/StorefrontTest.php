<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
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
            ProductVariant::factory()->for($p)->create(['price' => 17.50]);
        }

        $resp = $this->get(route('home'));
        $resp->assertOk();
        $resp->assertSee('Discover rare pressings & handmade goods');
        foreach ($active as $p) {
            $resp->assertSee($p->name);
            $resp->assertSee('$' . number_format(17.50, 2));
            $resp->assertSee('View details');
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
        $resp->assertSee('Shop the Attic collection');
        $resp->assertSee('Red Wool Hat');
        $resp->assertDontSee('Blue Cotton Scarf');
    }

    public function test_category_filter_shows_only_matching_products(): void
    {
        $records = ProductCategory::create(['name' => 'Records', 'slug' => 'records']);
        $apparel = ProductCategory::create(['name' => 'Apparel', 'slug' => 'apparel']);

        $recordProduct = Product::factory()->create(['name' => 'Lo-fi Vinyl', 'is_active' => true]);
        $apparelProduct = Product::factory()->create(['name' => 'Graphic Tee', 'is_active' => true]);
        ProductVariant::factory()->for($recordProduct)->create();
        ProductVariant::factory()->for($apparelProduct)->create();
        $recordProduct->categories()->attach($records);
        $apparelProduct->categories()->attach($apparel);

        $resp = $this->get(route('products.index', ['category' => 'records']));
        $resp->assertOk();
        $resp->assertSee('Filtered by');
        $resp->assertSee('Records');
        $resp->assertSee('Lo-fi Vinyl');
        $resp->assertDontSee('Graphic Tee');
    }

    public function test_product_show_page_displays_product(): void
    {
        $product = Product::factory()->create(['slug' => 'my-slug', 'is_active' => true]);
        ProductVariant::factory()->for($product)->create([
            'sku' => 'SKU-123',
            'price' => 22.00,
            'option_summary' => null,
        ]);

        $resp = $this->get(route('products.show', ['slug' => 'my-slug']));
        $resp->assertOk();
        $resp->assertSee($product->name);
        $resp->assertSee('Attic Store Exclusive');
        $resp->assertSee('Add to cart');
        $resp->assertSee('SKU-123');
    }
}
