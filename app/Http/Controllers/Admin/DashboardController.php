<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Order,ProductVariant,Inventory};

class DashboardController extends Controller
{
    public function index() {
        $today = now()->startOfDay();
        $todaysOrders = Order::where('created_at','>=',$today)->count();
        $revenue = Order::where('created_at','>=',$today)->sum('grand_total');
        $lowStock = ProductVariant::with('inventory')->get()->filter(fn($v)=>optional($v->inventory)->qty_on_hand < 3)->take(10);
        $unfulfilled = Order::where('status','paid')->orWhere('status','pending')->count();
        return view('admin/dashboard', compact('todaysOrders','revenue','lowStock','unfulfilled'));
    }
}
