<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{ProductVariant, CartItem};
use App\Services\CartService;

class CartController extends Controller
{
    public function add(Request $r, CartService $svc) {
        $variant = ProductVariant::findOrFail($r->input('variant_id'));
        $cart = $svc->resolve($r);
        $svc->add($cart, $variant, (int)$r->input('quantity',1));
        return redirect()->route('cart.view')->with('ok','Added to cart');
    }

    public function view(Request $r, CartService $svc) {
        $cart = $svc->resolve($r)->load('items.variant.product');
        $totals = $svc->totals($cart);
        return view('cart/view', compact('cart','totals'));
    }

    public function remove(Request $r, CartService $svc, CartItem $item) {
        $cart = $svc->resolve($r);
        if ($item->cart_id !== $cart->id) {
            abort(404);
        }
        $item->delete();
        return redirect()->route('cart.view')->with('ok','Removed from cart');
    }

    public function applyDiscount() {
        return back()->with('ok','Discounts stubbed for MVP.');
    }
}
