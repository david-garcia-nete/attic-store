<?php

namespace Tests\Feature;

use App\Models\{Cart, CartItem, Product, ProductVariant, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_start_page_renders(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Collector Jacket', 'price' => 120]);
        $variant = ProductVariant::factory()->for($product)->create(['price' => 135, 'weight_oz' => 8, 'sku' => 'JKT-001']);
        $cart = Cart::factory()->create(['user_id' => $user->id, 'session_id' => null]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 135.00,
        ]);

        $resp = $this->actingAs($user)->get(route('checkout.start'));
        $resp->assertOk();
        $resp->assertSee('Checkout');
        $resp->assertSee('Collector Jacket');
        $resp->assertSee('Total due today');
        $resp->assertSee('data-weight="8"', false);
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
