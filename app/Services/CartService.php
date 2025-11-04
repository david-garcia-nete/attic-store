<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\{Cart,CartItem,ProductVariant};

class CartService
{
    public function resolve(Request $r): Cart
    {
        if ($r->user()) {
            return Cart::firstOrCreate(['user_id'=>$r->user()->id]);
        }
        $sid = $r->session()->getId();
        return Cart::firstOrCreate(['session_id'=>$sid]);
    }

    public function add(Cart $cart, ProductVariant $variant, int $qty = 1): CartItem
    {
        $item = $cart->items()->firstOrCreate(
            ['product_variant_id'=>$variant->id],
            ['quantity'=>0, 'unit_price'=>$variant->priceEffective()]
        );
        $item->quantity += $qty;
        $item->unit_price = $variant->priceEffective();
        $item->save();
        return $item;
    }

    public function totals(Cart $cart): array
    {
        $subtotal = $cart->items->sum(fn($i) => $i->unit_price * $i->quantity);
        return [
            'subtotal' => $subtotal,
            'discount_total' => 0.0,
            'shipping_total' => 0.0,
            'tax_total' => 0.0,
            'grand_total' => $subtotal,
        ];
    }
}
