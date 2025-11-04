<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Product,ProductVariant,Inventory};

class ProductVariantController extends Controller
{
    public function store(Request $r) {
        $data = $r->validate([
            'product_id'=>'required|exists:products,id',
            'sku'=>'required|unique:product_variants,sku',
            'price'=>'nullable|numeric',
            'option_summary'=>'nullable|string',
            'weight_oz'=>'nullable|numeric',
            'length_in'=>'nullable|numeric',
            'width_in'=>'nullable|numeric',
            'height_in'=>'nullable|numeric',
            'qty_on_hand'=>'nullable|integer',
            'bin_location'=>'nullable|string',
        ]);
        $variant = ProductVariant::create($data);
        $variant->inventory()->create([
            'qty_on_hand'=>$data['qty_on_hand'] ?? 0,
            'qty_reserved'=>0,
            'bin_location'=>$data['bin_location'] ?? null,
        ]);
        return back()->with('ok','Variant created');
    }

    public function update(Request $r, ProductVariant $variant) {
        $variant->update($r->only('sku','price','option_summary','weight_oz','length_in','width_in','height_in','is_active'));
        if ($inv = $variant->inventory) {
            $inv->update($r->only('qty_on_hand','bin_location'));
        }
        return back()->with('ok','Variant updated');
    }

    public function destroy(ProductVariant $variant) {
        $variant->delete();
        return back()->with('ok','Variant deleted');
    }
}
