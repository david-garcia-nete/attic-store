<?php

namespace App\Http\Controllers;

use App\Models\{Product,ProductVariant};
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    public function home() {
        $products = Product::with('variants')->where('is_active',true)->latest()->take(8)->get();
        return view('home', compact('products'));
    }

    public function index(Request $r) {
        $q = Product::with('variants')->where('is_active',true);
        if ($term = $r->query('q')) {
            $q->where('name','like','%'.$term.'%');
        }
        $products = $q->paginate(12);
        return view('home', compact('products'));
    }

    public function show($slug) {
        $product = Product::with('variants.inventory')->where('slug',$slug)->firstOrFail();
        return view('product/show', compact('product'));
    }
}
