<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_products(): void
    {
        $p1 = Product::factory()->create(['name' => 'Widget A', 'price' => 12.5]);
        $p2 = Product::factory()->create(['name' => 'Widget B', 'price' => 3.49]);

        $resp = $this->get('/admin/products');
        $resp->assertOk();
        $resp->assertSee('Products');
        $resp->assertSee('Widget A');
        $resp->assertSee(number_format(12.5, 2));
        $resp->assertSee('Widget B');
        $resp->assertSee(number_format(3.49, 2));
        // Ensure the view rendered links
        $resp->assertSee(route('admin.products.create'), false);
        $resp->assertSee(route('admin.products.edit', $p1), false);
    }

    public function test_create_screen_renders(): void
    {
        $resp = $this->get(route('admin.products.create'));
        $resp->assertOk();
        $resp->assertSee('Create Product');
        $resp->assertSee('Use this screen to add a new product.', false);
    }

    public function test_edit_screen_renders_with_product_name(): void
    {
        $product = Product::factory()->create(['name' => 'Sample Item']);
        $category = ProductCategory::create(['name' => 'Vinyl', 'slug' => 'vinyl']);
        $product->categories()->attach($category);

        $resp = $this->get(route('admin.products.edit', $product));
        $resp->assertOk();
        $resp->assertSee('Edit Product');
        $resp->assertSee('Editing:', false);
        $resp->assertSee('Sample Item');
        $resp->assertSee('Vinyl');
    }

    public function test_store_creates_product_and_redirects_to_edit(): void
    {
        $payload = [
            'name' => 'New Product',
            'slug' => 'new-product-1234',
            'price' => 19.99,
            'description' => 'A shiny new product.',
        ];

        $resp = $this->post(route('admin.products.store'), $payload);
        $resp->assertRedirect();

        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'slug' => 'new-product-1234',
            'price' => 19.99,
            'is_active' => true,
        ]);

        $product = Product::where('slug', 'new-product-1234')->firstOrFail();
        $resp->assertRedirectToRoute('admin.products.edit', $product);
    }

    public function test_update_modifies_fields_and_redirects_back(): void
    {
        $product = Product::factory()->create([
            'name' => 'Old Name',
            'slug' => 'old-slug-100',
            'price' => 10.00,
            'description' => 'Old desc',
        ]);

        $payload = [
            'name' => 'Updated Name',
            'slug' => 'updated-slug-100',
            'price' => 15.25,
            'description' => 'Updated desc',
            'is_active' => false,
        ];

        // Provide a referer so back() knows where to go
        $resp = $this->from(route('admin.products.edit', $product))
            ->patch(route('admin.products.update', $product), $payload);

        $resp->assertRedirect(route('admin.products.edit', $product));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'slug' => 'updated-slug-100',
            'price' => 15.25,
            'description' => 'Updated desc',
            'is_active' => false,
        ]);
    }

    public function test_store_requires_unique_slug(): void
    {
        Product::factory()->create(['slug' => 'duplicate-slug']);

        $resp = $this->from(route('admin.products.create'))
            ->post(route('admin.products.store'), [
                'name' => 'Another Product',
                'slug' => 'duplicate-slug',
                'price' => 10.00,
            ]);

        $resp->assertRedirect(route('admin.products.create'));
        $resp->assertSessionHasErrors('slug');
    }

    public function test_destroy_deletes_and_redirects_to_index(): void
    {
        $product = Product::factory()->create();

        $resp = $this->delete(route('admin.products.destroy', $product));

        $resp->assertRedirectToRoute('admin.products.index');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
