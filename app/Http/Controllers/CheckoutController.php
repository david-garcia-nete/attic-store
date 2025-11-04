<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CheckoutService;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Stripe\StripeClient;
use App\Models\Order;

class CheckoutController extends Controller
{
    public function start(Request $r) {
        return view('checkout/start');
    }

    public function shipQuote(Request $r) {
        $oz = (int) $r->input('weight_oz', 12);
        $rate = $oz <= 8 ? 4.99 : ($oz <= 16 ? 6.49 : 8.99);
        return response()->json(['rate'=>$rate]);
    }

    public function payWithStripe(Request $r, CheckoutService $svc) {
        [$order, $amount] = $svc->createPendingOrder($r);
        $stripe = new StripeClient(config('cashier.secret', env('STRIPE_SECRET')));
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
        [$order, $amount] = $svc->createPendingOrder($r);
        $pp = new PayPalClient;
        $pp->setApiCredentials(config('paypal'));
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
        return view('checkout/thankyou', compact('order'));
    }
}
