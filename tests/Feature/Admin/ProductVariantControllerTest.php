<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\ProductVariantController;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ProductVariantControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_variant_and_inventory(): void
    {
        $this->actingAs(User::factory()->create());
        app('session')->start();

        $product = Product::factory()->create();

        $payload = [
            'product_id' => $product->id,
            'sku' => 'SKU-STORE-1',
            'price' => '12.50',
            'option_summary' => 'Size M',
            'qty_on_hand' => 7,
            'bin_location' => 'A1-B2',
        ];

        $request = Request::create('/admin/product-variants', 'POST', $payload);
        $request->setUserResolver(fn () => auth()->user());
        app()->instance('request', $request);
        request()->merge($payload);

        /** @var RedirectResponse $response */
        $response = app()->call([ProductVariantController::class, 'store']);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('Variant created', session('ok'));

        $variant = ProductVariant::where('sku', 'SKU-STORE-1')->first();
        $this->assertNotNull($variant);
        $this->assertSame(7, (int) $variant->inventory->qty_on_hand);
        $this->assertSame('A1-B2', $variant->inventory->bin_location);
    }

    public function test_update_mutates_variant_and_inventory(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        app('session')->start();

        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => 'SKU-OLD',
            'price' => 25.00,
        ]);
        $inventory = Inventory::factory()->for($variant, 'variant')->create([
            'qty_on_hand' => 4,
            'bin_location' => 'A1',
        ]);

        $payload = [
            'sku' => 'SKU-NEW',
            'price' => '30.00',
            'option_summary' => 'Updated summary',
            'qty_on_hand' => 9,
            'bin_location' => 'B2',
        ];

        $request = Request::create('/admin/product-variants/' . $variant->id, 'PUT', $payload);
        $request->setUserResolver(fn () => $user);
        app()->instance('request', $request);
        request()->merge($payload);
        URL::setPreviousUrl('http://localhost/admin/products/' . $product->id . '/edit');

        /** @var RedirectResponse $response */
        $response = app()->call([ProductVariantController::class, 'update'], ['variant' => $variant]);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('Variant updated', session('ok'));

        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'sku' => 'SKU-NEW',
            'price' => '30.00',
            'option_summary' => 'Updated summary',
        ]);
        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'qty_on_hand' => 9,
            'bin_location' => 'B2',
        ]);
    }

    public function test_destroy_deletes_variant(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        app('session')->start();

        $variant = ProductVariant::factory()->create();

        /** @var RedirectResponse $response */
        $response = app()->call([ProductVariantController::class, 'destroy'], ['variant' => $variant]);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('Variant deleted', session('ok'));
        $this->assertDatabaseMissing('product_variants', ['id' => $variant->id]);
    }
}
