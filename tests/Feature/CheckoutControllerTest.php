<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_start_redirects_when_cart_empty(): void
    {
        $resp = $this->get(route('checkout.start'));
        $resp->assertRedirect(route('cart.view'));
        $resp->assertSessionHas('ok', 'Add items to your cart before checking out.');
    }

    public function test_checkout_start_page_renders_with_cart_items(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $cart = Cart::factory()->create(['user_id' => $user->id, 'session_id' => null]);
        $product = Product::factory()->create(['price' => 25.00]);
        $variant = ProductVariant::factory()->for($product)->create(['price' => null, 'weight_oz' => 4]);
        Inventory::factory()->for($variant, 'variant')->create(['qty_on_hand' => 5, 'qty_reserved' => 0]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 25.00,
        ]);

        $resp = $this->get(route('checkout.start'));
        $resp->assertOk();
        $resp->assertViewIs('checkout.start');
        $resp->assertSee('Checkout');
        $resp->assertSee('$25.00');
    }

    public function test_ship_quote_returns_expected_rates(): void
    {
        $this->postJson(route('checkout.shipQuote'), ['weight_oz' => 7])
            ->assertOk()
            ->assertJson(['rate' => 4.99]);

        $this->postJson(route('checkout.shipQuote'), ['weight_oz' => 12])
            ->assertOk()
            ->assertJson(['rate' => 6.49]);

        $this->postJson(route('checkout.shipQuote'), ['weight_oz' => 20])
            ->assertOk()
            ->assertJson(['rate' => 8.99]);
    }
}
