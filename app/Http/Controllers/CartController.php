<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{ProductVariant, CartItem};
use App\Services\CartService;

class CartController extends Controller
{
    public function add(Request $r, CartService $svc) {
        $data = $r->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $variant = ProductVariant::with('inventory', 'product')->findOrFail($data['variant_id']);
        $cart = $svc->resolve($r);

        try {
            $svc->add($cart, $variant, (int) $data['quantity']);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['quantity' => $e->getMessage()])->withInput();
        }

        return redirect()->route('cart.view')->with('ok', $variant->product->name . ' added to cart.');
    }

    public function view(Request $r, CartService $svc) {
        $cart = $svc->resolve($r)->load('items.variant.product', 'items.variant.inventory');
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

    public function applyDiscount(Request $r, CartService $svc) {
        if ($r->boolean('remove')) {
            $svc->clearDiscount();
            return back()->with('ok', 'Discount removed.');
        }

        $data = $r->validate(['code' => 'required|string']);

        if (!$svc->applyDiscountCode($data['code'])) {
            return back()->withErrors(['code' => 'That discount code is invalid or expired.'])->withInput();
        }

        return back()->with('ok', 'Discount applied!');
    }
}
