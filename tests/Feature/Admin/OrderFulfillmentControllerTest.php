<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\OrderFulfillmentController;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class OrderFulfillmentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_fulfill_updates_inventory_and_status(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $variant = ProductVariant::factory()->create();
        $inventory = Inventory::factory()->for($variant, 'variant')->create([
            'qty_on_hand' => 10,
            'qty_reserved' => 4,
        ]);

        $order = Order::factory()->create(['status' => 'pending']);
        $order->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price' => 30.00,
            'line_total' => 60.00,
        ]);

        URL::setPreviousUrl('http://localhost/admin/orders/' . $order->id);

        /** @var RedirectResponse $response */
        $response = app()->call([OrderFulfillmentController::class, 'fulfill'], ['order' => $order->fresh('items.variant.inventory')]);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('Order fulfilled', session('ok'));

        $inventory->refresh();
        $this->assertSame(8, (int) $inventory->qty_on_hand);
        $this->assertSame(2, (int) $inventory->qty_reserved);

        $this->assertSame('fulfilled', $order->fresh()->status);
    }
}
