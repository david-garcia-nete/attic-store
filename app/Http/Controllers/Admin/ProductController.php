<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Product,ProductCategory};
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index() {
        $products = Product::latest()->paginate(15);
        return view('admin/products_index', compact('products'));
    }
    public function create() {
        $categories = ProductCategory::orderBy('name')->get();
        return view('admin/products_create', compact('categories'));
    }
    public function store(Request $r) {
        $data = $r->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'category_ids' => 'array',
            'category_ids.*' => 'exists:product_categories,id',
        ]);
        $product = Product::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'price' => $data['price'],
            'description' => $data['description'] ?? null,
            'is_active' => $r->boolean('is_active', true),
        ]);
        if ($r->hasFile('image')) { $product->addMediaFromRequest('image')->toMediaCollection(); }
        $product->categories()->sync($r->input('category_ids', []));
        return redirect()->route('admin.products.edit',$product)->with('ok','Product created');
    }
    public function edit(Product $product) {
        $product->load('variants.inventory', 'categories');
        $categories = ProductCategory::orderBy('name')->get();
        return view('admin/products_edit', compact('product','categories'));
    }
    public function update(Request $r, Product $product) {
        $data = $r->validate([
            'name' => 'required|string|max:255',
            'slug' => ['required','string','max:255', Rule::unique('products','slug')->ignore($product->id)],
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'category_ids' => 'array',
            'category_ids.*' => 'exists:product_categories,id',
        ]);
        $product->update([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'price' => $data['price'],
            'description' => $data['description'] ?? null,
            'is_active' => $r->boolean('is_active'),
        ]);
        $product->categories()->sync($r->input('category_ids', []));
        if ($r->hasFile('image')) { $product->clearMediaCollection(); $product->addMediaFromRequest('image')->toMediaCollection(); }
        return back()->with('ok','Saved');
    }
    public function destroy(Product $product) {
        $product->delete();
        return redirect()->route('admin.products.index')->with('ok','Deleted');
    }
}
