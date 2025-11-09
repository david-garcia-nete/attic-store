<?php

namespace Tests\Unit\Services;

use App\Models\{Cart, CartItem, Inventory, Order, Product, ProductVariant, User};
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class CheckoutServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUserRequest(User $user, array $data = []): Request
    {
        $req = Request::create('/', 'POST', $data);
        $req->setUserResolver(fn () => $user);
        $req->setLaravelSession(app('session')->driver());
        return $req;
    }

    public function test_create_pending_order_creates_items_and_reserves_inventory(): void
    {
        $user = User::factory()->create();

        $product = Product::factory()->create(['price' => 10.00]);
        $variant = ProductVariant::factory()->for($product)->create(['price' => 12.00]);
        $inv = Inventory::factory()->create([
            'product_variant_id' => $variant->id,
            'qty_on_hand' => 100,
            'qty_reserved' => 0,
        ]);

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 3,
            'unit_price' => 12.00,
        ]);

        $svc = new CheckoutService();
        [$order, $amount] = $svc->createPendingOrder($this->makeUserRequest($user, [
            'ship_name' => 'Ada Lovelace',
            'ship_line1' => '123 Code St',
            'ship_city' => 'London',
            'ship_state' => 'LDN',
            'ship_postal' => 'EC1A 1BB',
            'ship_country' => 'GB',
        ]));

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(36.00, (float)$order->subtotal);
        $this->assertEquals($order->grand_total, $amount);
        $this->assertCount(1, $order->items);
        $item = $order->items->first();
        $this->assertEquals($variant->id, $item->product_variant_id);
        $this->assertEquals(3, (int)$item->quantity);
        $this->assertEquals(36.00, (float)$item->line_total);

        $inv->refresh();
        $this->assertEquals(3, (int)$inv->qty_reserved, 'Inventory should be reserved');

        // Address created
        $this->assertDatabaseHas('addresses', [
            'addressable_id' => $order->id,
            'addressable_type' => Order::class,
            'type' => 'shipping',
            'name' => 'Ada Lovelace',
        ]);
    }

    public function test_create_pending_order_throws_if_insufficient_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10.00]);
        $variant = ProductVariant::factory()->for($product)->create(['price' => 12.00]);
        Inventory::factory()->create([
            'product_variant_id' => $variant->id,
            'qty_on_hand' => 1,
            'qty_reserved' => 0,
        ]);

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price' => 12.00,
        ]);

        $this->expectException(\RuntimeException::class);
        $svc = new CheckoutService();
        $svc->createPendingOrder($this->makeUserRequest($user));
    }
}
