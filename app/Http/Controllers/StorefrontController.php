<?php

namespace App\Http\Controllers;

use App\Models\{Product,ProductCategory};
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    public function home() {
        $products = Product::with('variants')->where('is_active', true)->latest()->take(8)->get();
        $categories = ProductCategory::orderBy('name')->get();

        return view('home', [
            'products' => $products,
            'categories' => $categories,
            'activeCategory' => null,
            'searchTerm' => null,
            'isLanding' => true,
        ]);
    }

    public function index(Request $r) {
        $query = Product::with('variants')->where('is_active', true);
        $categories = ProductCategory::orderBy('name')->get();
        $activeCategory = null;
        $term = trim((string) $r->query('q'));

        if ($slug = $r->query('category')) {
            $activeCategory = ProductCategory::where('slug', $slug)->firstOrFail();
            $query->whereHas('categories', function ($q) use ($activeCategory) {
                $q->where('product_category_id', $activeCategory->id);
            });
        }

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        $products = $query->paginate(12)->withQueryString();

        return view('home', [
            'products' => $products,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'searchTerm' => $term ?: null,
            'isLanding' => false,
        ]);
    }

    public function show($slug) {
        $product = Product::with('variants.inventory')->where('slug',$slug)->firstOrFail();
        return view('product/show', compact('product'));
    }
}
