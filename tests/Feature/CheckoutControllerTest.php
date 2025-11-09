<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_start_page_renders(): void
    {
        $resp = $this->get(route('checkout.start'));
        $resp->assertOk();
        $resp->assertSee('Checkout');
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
