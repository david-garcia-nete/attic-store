<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\OrderFulfillmentController;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminOrdersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->group(function () {
            Route::get('/admin/orders', [OrderController::class, 'index'])->name('admin.orders.index');
            Route::get('/admin/orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
            Route::post('/admin/orders/{order}', [OrderController::class, 'update'])->name('admin.orders.update');
            Route::post('/admin/orders/{order}/fulfill', [OrderFulfillmentController::class, 'fulfill'])->name('admin.orders.fulfill');
        });
    }

    public function test_index_lists_orders(): void
    {
        $orders = Order::factory()->count(3)->create();

        $resp = $this->get(route('admin.orders.index'));
        $resp->assertOk();
        foreach ($orders as $o) {
            $resp->assertSee($o->number);
        }
    }

    public function test_show_displays_order_details(): void
    {
        $product = Product::factory()->create(['name' => 'Widget']);
        $variant = ProductVariant::factory()->for($product)->create(['sku' => 'WID-001']);
        $order = Order::factory()->create();

        // Create an item with a bin snapshot and totals
        $inventory = Inventory::factory()->for($variant, 'variant')->create(['bin_location' => 'A01-B02']);
        $item = $order->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price' => 12.50,
            'line_total' => 25.00,
            'bin_snapshot' => $inventory->bin_location,
        ]);

        $resp = $this->get(route('admin.orders.show', $order));
        $resp->assertOk();
        $resp->assertSee('Order ' . $order->number);
        $resp->assertSee('Widget');
        $resp->assertSee('WID-001');
        $resp->assertSee('25.00');
        $resp->assertSee('A01-B02');
    }

    public function test_update_changes_order_status(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $resp = $this->from('/admin/orders')
            ->post(route('admin.orders.update', $order), ['status' => 'paid']);

        $resp->assertRedirect('/admin/orders');
        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_fulfill_commits_inventory_and_marks_order_fulfilled(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create();
        $inventory = Inventory::factory()->for($variant, 'variant')->create([
            'qty_on_hand' => 10,
            'qty_reserved' => 2,
        ]);
        $order = Order::factory()->create(['status' => 'paid']);
        $order->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price' => 5.00,
            'line_total' => 10.00,
            'bin_snapshot' => $inventory->bin_location,
        ]);

        $resp = $this->from(route('admin.orders.show', $order))
            ->post(route('admin.orders.fulfill', $order));

        $resp->assertRedirect(route('admin.orders.show', $order));

        $inventory->refresh();
        $this->assertSame(8, (int) $inventory->qty_on_hand);
        $this->assertSame(0, (int) $inventory->qty_reserved);
        $this->assertSame('fulfilled', $order->fresh()->status);
    }
}
