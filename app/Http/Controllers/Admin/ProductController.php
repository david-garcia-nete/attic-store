<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Product,ProductVariant,Inventory};

class ProductController extends Controller
{
    public function index() {
        $products = Product::latest()->paginate(15);
        return view('admin/products_index', compact('products'));
    }
    public function create() { return view('admin/products_create'); }
    public function store(Request $r) {
        $data = $r->validate(['name'=>'required','slug'=>'required|unique:products','price'=>'required|numeric','description'=>'nullable']);
        $product = Product::create($data);
        if ($r->hasFile('image')) { $product->addMediaFromRequest('image')->toMediaCollection(); }
        return redirect()->route('products.edit',$product)->with('ok','Product created');
    }
    public function edit(Product $product) { $product->load('variants.inventory'); return view('admin/products_edit', compact('product')); }
    public function update(Request $r, Product $product) {
        $product->update($r->only('name','slug','price','description','is_active'));
        if ($r->hasFile('image')) { $product->clearMediaCollection(); $product->addMediaFromRequest('image')->toMediaCollection(); }
        return back()->with('ok','Saved');
    }
    public function destroy(Product $product) {
        $product->delete();
        return redirect()->route('products.index')->with('ok','Deleted');
    }
}
