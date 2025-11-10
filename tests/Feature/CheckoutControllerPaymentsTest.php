<?php

namespace Tests\Feature;

use App\Models\{Cart, Inventory, Order, Payment, Product, ProductVariant, User};
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutControllerPaymentsTest extends TestCase
{
    use RefreshDatabase;

    private function seedCartFor(User $user): void
    {
        $product = Product::factory()->create(['name' => 'Limited Pressing', 'price' => 30]);
        $variant = ProductVariant::factory()->for($product)->create(['price' => 28.00, 'weight_oz' => 7, 'sku' => 'VINYL-001']);
        $inventory = Inventory::factory()->for($variant, 'variant')->create([
            'qty_on_hand' => 5,
            'qty_reserved' => 0,
            'bin_location' => 'A01-B02',
        ]);
        $cart = Cart::factory()->create(['user_id' => $user->id, 'session_id' => null]);

        app(CartService::class)->add($cart, $variant, 2);
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
        $user = User::factory()->create();
        $this->seedCartFor($user);
        $this->bindFakeStripe();

        $resp = $this->actingAs($user)->post(route('checkout.stripe'), [
            'ship_name' => 'Ada Lovelace',
            'ship_line1' => '123 Code St',
            'ship_city' => 'Math',
            'ship_state' => 'UK',
            'ship_postal' => 'A1B2C3',
        ]);

        $resp->assertOk();
        $resp->assertViewIs('checkout.stripe');
        $resp->assertViewHasAll(['clientSecret', 'order']);
        $resp->assertSee('Pay with Stripe');

        $this->assertDatabaseHas('payments', [
            'provider' => 'stripe',
            'status' => 'authorized',
        ]);
    }

    public function test_pay_with_paypal_redirects_to_approve_and_records_payment(): void
    {
        $user = User::factory()->create();
        $this->seedCartFor($user);
        $this->bindFakePayPal();

        $resp = $this->actingAs($user)->post(route('checkout.paypal'), [
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
        $order = Order::factory()->create(['subtotal' => 50.00, 'shipping_total' => 8.99, 'tax_total' => 3.50, 'grand_total' => 62.49]);
        $product = Product::factory()->create(['name' => 'Mystery Bundle']);
        $variant = ProductVariant::factory()->for($product)->create(['sku' => 'BNDL-001']);
        $order->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 50.00,
            'line_total' => 50.00,
            'bin_snapshot' => 'B01-C03',
        ]);
        $this->get(route('checkout.thankyou', ['order' => $order->id]))
            ->assertOk()
            ->assertSee('Thank you!')
            ->assertSee($order->number)
            ->assertSee('Mystery Bundle')
            ->assertSee('$50.00')
            ->assertSee('$8.99')
            ->assertSee('$3.50')
            ->assertSee('$62.49');
    }
}
