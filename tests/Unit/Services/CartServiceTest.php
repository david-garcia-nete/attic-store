<?php

namespace Tests\Unit\Services;

use App\Models\{Cart, Product, ProductVariant};
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function makeRequestWithUser($user = null): Request
    {
        $request = Request::create('/');
        $request->setLaravelSession(app('session')->driver());
        if ($user) {
            $request->setUserResolver(fn () => $user);
        }
        return $request;
    }

    public function test_resolve_creates_cart_for_session_and_user(): void
    {
        $svc = new CartService();

        // Session-based cart
        $this->get('/');
        $request = request();
        $cart = $svc->resolve($request);
        $this->assertInstanceOf(Cart::class, $cart);
        $this->assertNotNull($cart->id);
        $this->assertEquals($request->session()->getId(), $cart->session_id);

        // User-based cart
        $user = \App\Models\User::factory()->create();
        $req2 = $this->makeRequestWithUser($user);
        $cart2 = $svc->resolve($req2);
        $this->assertEquals($user->id, $cart2->user_id);
    }

    public function test_add_merges_quantities_and_sets_effective_price(): void
    {
        $svc = new CartService();
        $this->get('/');
        $req = request();
        $cart = $svc->resolve($req);

        $product = Product::factory()->create(['price' => 20.00]);
        $variant = ProductVariant::factory()->for($product)->create(['price' => null]);

        $item1 = $svc->add($cart, $variant, 1);
        $this->assertSame(1, (int)$item1->quantity);
        $this->assertEquals(20.00, (float)$item1->unit_price);

        $item2 = $svc->add($cart, $variant, 3);
        $this->assertSame($item1->id, $item2->id, 'Should merge into same item');
        $this->assertSame(4, (int)$item2->quantity);
        $this->assertEquals(20.00, (float)$item2->unit_price);
    }

    public function test_totals_calculates_subtotal_and_grand_total(): void
    {
        $svc = new CartService();
        $this->get('/');
        $cart = $svc->resolve(request());

        $product = Product::factory()->create(['price' => 5.50]);
        $v1 = ProductVariant::factory()->for($product)->create(['price' => 6.00]);
        $v2 = ProductVariant::factory()->for($product)->create(['price' => null]);

        $svc->add($cart, $v1, 2); // 2 * 6 = 12
        $svc->add($cart, $v2, 3); // 3 * 5.5 = 16.5

        $totals = $svc->totals($cart->load('items.variant'));
        $this->assertEquals(28.5, (float)$totals['subtotal']);
        $this->assertEquals(0.0, (float)$totals['discount_total']);
        $this->assertGreaterThan(0, $totals['shipping_total']);
        $this->assertEquals(round(0.07 * 28.5, 2), round($totals['tax_total'], 2));
        $expectedGrand = round(28.5 - 0.0 + $totals['shipping_total'] + $totals['tax_total'], 2);
        $this->assertEquals($expectedGrand, (float)$totals['grand_total']);
        $this->assertSame(60, $totals['total_weight_oz']);
    }
}
