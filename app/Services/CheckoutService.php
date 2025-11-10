<?php

namespace App\Services;

use App\Models\{Cart,Order,OrderItem,Inventory,Address};
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CheckoutService
{
    public function createPendingOrder(Request $r): array
    {
        $cartService = app(CartService::class);
        $cart = $cartService->resolve($r)->load('items.variant.inventory','items.variant.product');

        if ($cart->items->isEmpty()) {
            throw new \RuntimeException('Your cart is empty.');
        }

        $totals = $cartService->totals($cart);

        $order = Order::create([
            'number' => strtoupper(Str::random(10)),
            'user_id' => optional($r->user())->id,
            'subtotal' => $totals['subtotal'],
            'discount_total' => $totals['discount_total'],
            'shipping_total' => $totals['shipping_total'],
            'tax_total' => $totals['tax_total'],
            'grand_total' => $totals['grand_total'],
            'status' => 'pending',
        ]);

        DB::transaction(function () use ($cart, $order) {
            foreach ($cart->items as $ci) {
                $inv = $ci->variant->inventory()->lockForUpdate()->first();
                if (!$inv) { throw new \RuntimeException('No inventory record for variant'); }
                $inv->reserve($ci->quantity);
                $order->items()->create([
                    'product_variant_id' => $ci->product_variant_id,
                    'quantity' => $ci->quantity,
                    'unit_price' => $ci->unit_price,
                    'line_total' => $ci->unit_price * $ci->quantity,
                    'bin_snapshot' => $inv->bin_location,
                ]);
            }
        });

        // Shipping & billing address are captured on frontend; stub here
        if ($name = $r->input('ship_name')) {
            $order->addresses()->create([
                'type' => 'shipping',
                'name' => $name,
                'line1' => $r->input('ship_line1',''),
                'line2' => $r->input('ship_line2'),
                'city' => $r->input('ship_city',''),
                'state' => $r->input('ship_state',''),
                'postal_code' => $r->input('ship_postal',''),
                'country' => $r->input('ship_country','US'),
                'phone' => $r->input('ship_phone'),
            ]);
        }

        return [$order, $order->grand_total];
    }
}
