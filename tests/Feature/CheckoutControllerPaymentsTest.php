<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutControllerPaymentsTest extends TestCase
{
    use RefreshDatabase;

    private function seedCartWithItem(): array
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $product = Product::factory()->create(['price' => 42.00]);
        $variant = ProductVariant::factory()->for($product)->create(['price' => null, 'weight_oz' => 6]);
        Inventory::factory()->for($variant, 'variant')->create(['qty_on_hand' => 5, 'qty_reserved' => 0]);

        $this->post(route('cart.add'), [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertRedirect(route('cart.view'));

        return [$user, $variant];
    }

    private function bindFakeStripe(): void
    {
        // Minimal fake Stripe client with the API surface we use
        $intent = new class {
            public string $id = 'pi_test_123';
            public string $client_secret = 'cs_test_abc';
            public function toArray(): array { return ['id' => $this->id, 'client_secret' => $this->client_secret]; }
        };

        $paymentIntents = new class($intent) {
            private $intent;
            public function __construct($intent) { $this->intent = $intent; }
            public function create(array $params) { return $this->intent; }
        };

        $stripe = new class($paymentIntents) {
            public $paymentIntents;
            public function __construct($paymentIntents) { $this->paymentIntents = $paymentIntents; }
        };

        app()->instance(\Stripe\StripeClient::class, $stripe);
    }

    private function bindFakePayPal(string $approveUrl = 'https://paypal.test/approve', string $orderId = 'PP-ORDER-1'): void
    {
        $pp = new class($approveUrl, $orderId) {
            private string $approveUrl; private string $orderId;
            public function __construct($approveUrl, $orderId){ $this->approveUrl=$approveUrl; $this->orderId=$orderId; }
            public function getAccessToken(){ return ['access_token' => 'fake', 'expires_in' => 3600]; }
            public function createOrder(array $payload){
                return [
                    'id' => $this->orderId,
                    'links' => [
                        ['rel' => 'approve', 'href' => $this->approveUrl],
                        ['rel' => 'self', 'href' => 'https://paypal.test/order/'.$this->orderId],
                    ],
                ];
            }
        };
        app()->instance(\Srmklive\PayPal\Services\PayPal::class, $pp);
    }

    public function test_pay_with_stripe_creates_payment_and_renders_view(): void
    {
        [$user, $variant] = $this->seedCartWithItem();
        $this->bindFakeStripe();

        $resp = $this->post(route('checkout.stripe'), [
            'ship_name' => 'Ada Lovelace',
            'ship_line1' => '123 Code St',
            'ship_city' => 'Math',
            'ship_state' => 'UK',
            'ship_postal' => 'A1B2C3',
        ]);

        $resp->assertOk();
        $resp->assertViewIs('checkout.stripe');
        $resp->assertViewHasAll(['clientSecret', 'order']);

        $this->assertDatabaseHas('payments', [
            'provider' => 'stripe',
            'status' => 'authorized',
        ]);

        $variant->refresh();
        $this->assertSame(1, $variant->inventory->qty_reserved);
    }

    public function test_pay_with_paypal_redirects_to_approve_and_records_payment(): void
    {
        $this->seedCartWithItem();
        $this->bindFakePayPal();

        $resp = $this->post(route('checkout.paypal'), [
            'ship_name' => 'Grace Hopper',
        ]);

        $resp->assertRedirect('https://paypal.test/approve');

        $this->assertDatabaseHas('payments', [
            'provider' => 'paypal',
            'status' => 'authorized',
            'provider_ref' => 'PP-ORDER-1',
        ]);
    }

    public function test_thank_you_page_renders(): void
    {
        $order = Order::factory()->create(['subtotal' => 40, 'discount_total' => 5, 'shipping_total' => 6.49, 'tax_total' => 2.45, 'grand_total' => 43.94]);
        $product = Product::factory()->create(['name' => 'Indie Vinyl']);
        $variant = ProductVariant::factory()->for($product)->create(['sku' => 'VIN-001', 'price' => 40]);
        $order->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 40,
            'line_total' => 40,
            'bin_snapshot' => 'A01-B02',
        ]);
        $this->get(route('checkout.thankyou', ['order' => $order->id]))
            ->assertOk()
            ->assertSee('Thank you')
            ->assertSee($order->number)
            ->assertSee('Subtotal')
            ->assertSee('$' . number_format($order->grand_total, 2));
    }
}
