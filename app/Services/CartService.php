<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\{Cart,CartItem,ProductVariant,Discount};

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
        $item = $cart->items()->firstOrNew(
            ['product_variant_id' => $variant->id],
            ['quantity' => 0, 'unit_price' => $variant->priceEffective()]
        );

        $inventory = $variant->inventory;
        if ($inventory) {
            $available = max(0, (int) $inventory->qty_on_hand - (int) $inventory->qty_reserved);
            if (($item->quantity + $qty) > $available) {
                throw new \RuntimeException("Only {$available} units remaining for {$variant->sku}.");
            }
        }

        $item->quantity += $qty;
        $item->unit_price = $variant->priceEffective();
        $item->save();

        return $item;
    }

    public function totals(Cart $cart): array
    {
        $cart->loadMissing('items.variant');

        $subtotal = $cart->items->sum(fn ($i) => $i->unit_price * $i->quantity);
        $weightOz = (int) round($cart->items->sum(function ($i) {
            $weight = $i->variant->weight_oz ?? 12;
            return $weight * $i->quantity;
        }));

        $discountData = session('cart_discount');
        $discountTotal = 0.0;
        $shippingTotal = $this->shippingQuoteFor($weightOz);

        if ($discountData) {
            switch ($discountData['type']) {
                case 'pct':
                    $discountTotal = round($subtotal * ((float) $discountData['value'] / 100), 2);
                    break;
                case 'fixed':
                    $discountTotal = min($subtotal, (float) $discountData['value']);
                    break;
                case 'shipping':
                    $shippingTotal = 0.0;
                    break;
            }
        }

        $taxable = max($subtotal - $discountTotal, 0);
        $taxTotal = round($taxable * 0.07, 2);
        $grandTotal = max($taxable + $shippingTotal + $taxTotal, 0);

        return [
            'subtotal' => round($subtotal, 2),
            'discount_total' => round($discountTotal, 2),
            'discount_code' => $discountData['code'] ?? null,
            'discount_label' => $discountData['label'] ?? null,
            'shipping_total' => round($shippingTotal, 2),
            'tax_total' => round($taxTotal, 2),
            'grand_total' => round($grandTotal, 2),
            'total_weight_oz' => $weightOz,
        ];
    }

    public function shippingQuoteFor(int $weightOz): float
    {
        if ($weightOz <= 0) {
            return 0.0;
        }

        if ($weightOz <= 8) {
            return 4.99;
        }

        if ($weightOz <= 16) {
            return 6.49;
        }

        if ($weightOz <= 48) {
            return 8.99;
        }

        return 12.99;
    }

    public function applyDiscountCode(string $code): bool
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return false;
        }

        $discount = Discount::where('code', $code)
            ->where('active', true)
            ->where(function ($query) {
                $now = now();
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($query) {
                $now = now();
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->first();

        $labels = [
            'pct' => fn ($value) => number_format($value, 0) . "% off your order",
            'fixed' => fn ($value) => '$' . number_format($value, 2) . ' off your order',
        ];

        $payload = null;

        if ($discount) {
            $payload = [
                'code' => $discount->code,
                'type' => $discount->type,
                'value' => (float) $discount->value,
                'label' => isset($labels[$discount->type]) ? $labels[$discount->type]($discount->value) : null,
            ];
        } elseif ($code === 'FREESHIP') {
            $payload = [
                'code' => 'FREESHIP',
                'type' => 'shipping',
                'value' => null,
                'label' => 'Free shipping',
            ];
        } elseif ($code === 'SAVE10') {
            $payload = [
                'code' => 'SAVE10',
                'type' => 'pct',
                'value' => 10,
                'label' => '10% off your order',
            ];
        }

        if (!$payload) {
            return false;
        }

        session(['cart_discount' => $payload]);

        return true;
    }

    public function clearDiscount(): void
    {
        session()->forget('cart_discount');
    }
}
