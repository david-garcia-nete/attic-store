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

    public function test_home_lists_active_products_with_product_cards(): void
    {
        $category = ProductCategory::factory()->create(['name' => 'Vinyl', 'slug' => 'vinyl']);
        $active = Product::factory()->count(2)->create(['description' => 'A lovely piece.']);
        foreach ($active as $index => $product) {
            $product->categories()->attach($category->id);
            ProductVariant::factory()->for($product)->create([
                'price' => $index === 0 ? 18.00 : null,
                'weight_oz' => 5 + $index,
            ]);
        }
        $inactive = Product::factory()->create(['is_active' => false, 'name' => 'Archived Product']);
        ProductVariant::factory()->for($inactive)->create();

        $resp = $this->get(route('home'));
        $resp->assertOk();
        $resp->assertSee('Discover rare pressings & handmade goods');
        foreach ($active as $p) {
            $resp->assertSee($p->name);
            $resp->assertSee('View details');
        }
        $resp->assertSee('$18.00'); // lowest variant price shown on product card
        $resp->assertDontSee('Archived Product');
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
        $resp->assertSee('Showing results for');
        $resp->assertSee('Red Wool Hat');
        $resp->assertDontSee('Blue Cotton Scarf');
    }

    public function test_category_filter_shows_only_matching_products(): void
    {
        $vinyl = ProductCategory::factory()->create(['name' => 'Vinyl', 'slug' => 'vinyl']);
        $apparel = ProductCategory::factory()->create(['name' => 'Apparel', 'slug' => 'apparel']);

        $record = Product::factory()->create(['name' => 'Rare Record']);
        $record->categories()->attach($vinyl->id);
        ProductVariant::factory()->for($record)->create();

        $shirt = Product::factory()->create(['name' => 'Graphic Tee']);
        $shirt->categories()->attach($apparel->id);
        ProductVariant::factory()->for($shirt)->create();

        $resp = $this->get(route('products.index', ['category' => 'vinyl']));
        $resp->assertOk();
        $resp->assertSee('Rare Record');
        $resp->assertDontSee('Graphic Tee');
        $resp->assertSee('Filtered by');
    }

    public function test_product_show_page_displays_product(): void
    {
        $product = Product::factory()->create(['slug' => 'my-slug', 'is_active' => true]);
        ProductVariant::factory()->for($product)->create([
            'sku' => 'SKU-123',
            'price' => 22.50,
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
