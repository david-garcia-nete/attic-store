<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\{CheckoutService, CartService};
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Stripe\StripeClient;
use App\Models\Order;

class CheckoutController extends Controller
{
    public function start(Request $r, CartService $svc) {
        $cart = $svc->resolve($r)->load('items.variant.product');

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.view')->with('ok', 'Add items to your cart before checking out.');
        }

        $totals = $svc->totals($cart);

        return view('checkout/start', compact('cart', 'totals'));
    }

    public function shipQuote(Request $r, CartService $svc) {
        $cart = $svc->resolve($r)->load('items.variant');
        $weightFromCart = (int) round($cart->items->sum(function ($item) {
            $weight = $item->variant->weight_oz ?? 12;
            return $weight * $item->quantity;
        }));

        $weight = (int) $r->input('weight_oz', $weightFromCart);
        $rate = $svc->shippingQuoteFor($weight);

        return response()->json(['rate' => round($rate, 2)]);
    }

    public function payWithStripe(Request $r, CheckoutService $svc) {
        try {
            [$order, $amount] = $svc->createPendingOrder($r);
        } catch (\RuntimeException $e) {
            return redirect()->route('cart.view')->withErrors(['cart' => $e->getMessage()]);
        }
        $stripe = app()->bound(StripeClient::class)
            ? app(StripeClient::class)
            : new StripeClient(config('cashier.secret', env('STRIPE_SECRET')));
        $intent = $stripe->paymentIntents->create([
            'amount' => (int)round($amount * 100),
            'currency' => 'usd',
            'metadata' => ['order_id'=>$order->id],
            'automatic_payment_methods' => ['enabled' => true],
        ]);
        $order->payments()->create([
            'provider'=>'stripe','provider_ref'=>$intent->id,'status'=>'authorized','amount'=>$amount,'payload'=>$intent->toArray()
        ]);
        return view('checkout/stripe', ['clientSecret'=>$intent->client_secret, 'order'=>$order]);
    }

    public function payWithPayPal(Request $r, CheckoutService $svc) {
        try {
            [$order, $amount] = $svc->createPendingOrder($r);
        } catch (\RuntimeException $e) {
            return redirect()->route('cart.view')->withErrors(['cart' => $e->getMessage()]);
        }
        $pp = app()->bound(PayPalClient::class) ? app(PayPalClient::class) : tap(new PayPalClient, function ($pp) {
            $pp->setApiCredentials(config('paypal'));
        });
        $pp->getAccessToken();
        $resp = $pp->createOrder([
            'intent' => 'CAPTURE',
            'purchase_units' => [[ 'amount' => ['currency_code'=>'USD', 'value'=>number_format($amount,2)] ]],
            'application_context' => ['return_url'=>route('checkout.thankyou',['order'=>$order->id]), 'cancel_url'=>route('cart.view')],
        ]);
        $approve = collect($resp['links'])->firstWhere('rel','approve')['href'] ?? '/cart';
        $order->payments()->create(['provider'=>'paypal','provider_ref'=>$resp['id'] ?? null,'status'=>'authorized','amount'=>$amount,'payload'=>$resp]);
        return redirect($approve);
    }

    public function thankYou(Order $order) {
        $order->load('items.variant.product');
        return view('checkout/thankyou', compact('order'));
    }
}
