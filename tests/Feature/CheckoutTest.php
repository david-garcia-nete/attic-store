<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_ship_quote_returns_expected_rates(): void
    {
        $response = $this->post(route('checkout.shipQuote'), ['weight_oz' => 8]);
        $response->assertOk()->assertJson(['rate' => 4.99]);

        $response = $this->post(route('checkout.shipQuote'), ['weight_oz' => 9]);
        $response->assertOk()->assertJson(['rate' => 6.49]);

        $response = $this->post(route('checkout.shipQuote'), ['weight_oz' => 17]);
        $response->assertOk()->assertJson(['rate' => 8.99]);
    }

    public function test_thank_you_page_displays_order_number(): void
    {
        $order = Order::create([
            'number' => strtoupper(Str::random(10)),
            'user_id' => null,
            'subtotal' => 10.00,
            'discount_total' => 0.00,
            'shipping_total' => 0.00,
            'tax_total' => 0.00,
            'grand_total' => 10.00,
            'status' => 'pending',
        ]);

        $resp = $this->get(route('checkout.thankyou', ['order' => $order->id]));
        $resp->assertOk();
        $resp->assertSee($order->number);
    }
}
