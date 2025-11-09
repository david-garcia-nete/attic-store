<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\ProductVariantController;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminProductVariantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Route::middleware('web')->group(function () {
            Route::post('/admin/variants', [ProductVariantController::class, 'store'])->name('admin.variants.store');
            Route::post('/admin/variants/{variant}', [ProductVariantController::class, 'update'])->name('admin.variants.update');
            Route::delete('/admin/variants/{variant}', [ProductVariantController::class, 'destroy'])->name('admin.variants.destroy');
        });
    }

    public function test_store_creates_variant_and_inventory(): void
    {
        $product = Product::factory()->create();

        $payload = [
            'product_id' => $product->id,
            'sku' => 'SKU-123',
            'price' => 19.99,
            'option_summary' => 'Size M / Red',
            'qty_on_hand' => 7,
            'bin_location' => 'A10-B20',
        ];

        $resp = $this->from('/admin/products/'.$product->id)
            ->post(route('admin.variants.store'), $payload);

        $resp->assertRedirect('/admin/products/'.$product->id);

        $variant = ProductVariant::where('sku', 'SKU-123')->firstOrFail();
        $this->assertSame($product->id, $variant->product_id);
        $this->assertEquals(19.99, (float) $variant->price);

        $inv = $variant->inventory;
        $this->assertNotNull($inv);
        $this->assertSame(7, (int) $inv->qty_on_hand);
        $this->assertSame(0, (int) $inv->qty_reserved);
        $this->assertSame('A10-B20', $inv->bin_location);
    }

    public function test_update_modifies_variant_and_inventory(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create([
            'sku' => 'OLD',
            'price' => 10.00,
            'option_summary' => 'Old',
        ]);
        $inv = Inventory::factory()->for($variant, 'variant')->create([
            'qty_on_hand' => 3,
            'bin_location' => 'A01-B01',
        ]);

        $resp = $this->from('/admin/products/'.$product->id)
            ->post(route('admin.variants.update', $variant), [
                'sku' => 'NEW-1',
                'price' => 11.50,
                'option_summary' => 'New',
                'qty_on_hand' => 9,
                'bin_location' => 'C03-D04',
            ]);
        $resp->assertRedirect('/admin/products/'.$product->id);

        $variant->refresh();
        $inv->refresh();
        $this->assertSame('NEW-1', $variant->sku);
        $this->assertEquals(11.50, (float) $variant->price);
        $this->assertSame('New', $variant->option_summary);
        $this->assertSame(9, (int) $inv->qty_on_hand);
        $this->assertSame('C03-D04', $inv->bin_location);
    }

    public function test_destroy_deletes_variant(): void
    {
        $variant = ProductVariant::factory()->create();
        Inventory::factory()->for($variant, 'variant')->create();

        $resp = $this->from('/admin/products/'.$variant->product_id)
            ->delete(route('admin.variants.destroy', $variant));

        $resp->assertRedirect('/admin/products/'.$variant->product_id);
        $this->assertDatabaseMissing('product_variants', ['id' => $variant->id]);
        // inventory should be gone via cascade or orphaned depending on schema; we assert variant is gone
    }
}
