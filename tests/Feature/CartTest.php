<?php

namespace Tests\Feature;

use App\Models\{Cart, CartItem, Product, ProductVariant, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_adds_variant_to_cart_and_redirects(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::factory()->for(Product::factory()->state(['price' => 12.50]))->create([
            'price' => 15.00,
        ]);

        $response = $this->actingAs($user)->post(route('cart.add'), [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect(route('cart.view'));

        $cart = Cart::firstWhere('user_id', $user->id);
        $this->assertNotNull($cart);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price' => 15.00, // uses variant price when present
        ]);
    }

    public function test_adding_same_variant_merges_quantity(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::factory()->for(Product::factory()->state(['price' => 9.99]))->create(['price' => null]);
        // first add (qty 1)
        $this->actingAs($user)->post(route('cart.add'), [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertRedirect(route('cart.view'));
        // second add (qty 3)
        $this->actingAs($user)->post(route('cart.add'), [
            'variant_id' => $variant->id,
            'quantity' => 3,
        ])->assertRedirect(route('cart.view'));

        $cart = Cart::firstWhere('user_id', $user->id);
        $item = CartItem::where('cart_id', $cart->id)->where('product_variant_id', $variant->id)->first();
        $this->assertNotNull($item);
        $this->assertSame(4, (int)$item->quantity, 'Quantities should merge');
        $this->assertEquals(9.99, (float)$item->unit_price, 'Falls back to product price when variant price null');
    }

    public function test_remove_item_from_cart(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $cart = Cart::factory()->create(['user_id' => $user->id, 'session_id' => null]);
        $variant = ProductVariant::factory()->for(Product::factory())->create();
        $item = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price' => 5.00,
        ]);

        $response = $this->delete(route('cart.remove', ['item' => $item->id]));
        $response->assertRedirect(route('cart.view'));
        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_adding_inactive_variant_still_adds_item_current_behavior(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::factory()->for(Product::factory())->create(['is_active' => false]);

        $this->actingAs($user)->post(route('cart.add'), [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertRedirect(route('cart.view'));

        $cart = Cart::firstWhere('user_id', $user->id);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);
    }
}
