<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\ProductController;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminProductsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Define minimal product admin routes just for testing
        Route::middleware('web')->group(function () {
            Route::get('/admin/products', [ProductController::class, 'index'])->name('products.index');
            Route::get('/admin/products/create', [ProductController::class, 'create'])->name('products.create');
            Route::post('/admin/products', [ProductController::class, 'store'])->name('products.store');
            Route::get('/admin/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
            Route::patch('/admin/products/{product}', [ProductController::class, 'update'])->name('products.update');
            Route::delete('/admin/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        });
    }

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
        $resp->assertSee('/admin/products/create', false);
        $resp->assertSee('/admin/products/'.$p1->id.'/edit', false);
    }

    public function test_store_creates_product_and_redirects_to_edit(): void
    {
        $payload = [
            'name' => 'New Product',
            'slug' => 'new-product-1234',
            'price' => 19.99,
            'description' => 'A shiny new product.',
        ];

        $resp = $this->post(route('products.store'), $payload);
        $resp->assertRedirect();

        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'slug' => 'new-product-1234',
            'price' => 19.99,
        ]);

        $product = Product::where('slug', 'new-product-1234')->firstOrFail();
        $resp->assertRedirectToRoute('products.edit', $product);
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
        $resp = $this->from(route('products.edit', $product))
            ->patch(route('products.update', $product), $payload);

        $resp->assertRedirect(route('products.edit', $product));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'slug' => 'updated-slug-100',
            'price' => 15.25,
            'description' => 'Updated desc',
            'is_active' => false,
        ]);
    }

    public function test_destroy_deletes_and_redirects_to_index(): void
    {
        $product = Product::factory()->create();

        $resp = $this->delete(route('products.destroy', $product));

        $resp->assertRedirectToRoute('products.index');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
