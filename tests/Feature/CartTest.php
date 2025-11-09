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

    public function test_cart_view_renders_with_totals(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $cart = Cart::factory()->create(['user_id' => $user->id, 'session_id' => null]);
        $variant = ProductVariant::factory()->for(Product::factory(['price' => 10]))->create(['price' => 12]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price' => 12.00,
        ]);

        $resp = $this->get(route('cart.view'));
        $resp->assertOk();
        $resp->assertSee('Your Cart');
        $resp->assertSee(number_format(24.00, 2));
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

    public function test_remove_item_returns_404_if_item_not_in_cart(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $cart = Cart::factory()->create(['user_id' => $user->id, 'session_id' => null]);

        $otherCart = Cart::factory()->create();
        $variant = ProductVariant::factory()->for(Product::factory())->create();
        $item = CartItem::factory()->create([
            'cart_id' => $otherCart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 5.00,
        ]);

        $this->delete(route('cart.remove', ['item' => $item->id]))->assertNotFound();
    }

    public function test_apply_discount_post_redirects_back_with_status(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->from(route('cart.view'))
            ->post(route('cart.discount'))
            ->assertRedirect(route('cart.view'))
            ->assertSessionHas('ok', 'Discounts stubbed for MVP.');
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
