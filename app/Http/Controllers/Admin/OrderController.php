<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderController extends Controller
{
    public function index() {
        $orders = Order::latest()->paginate(20);
        return view('admin/orders_index', compact('orders'));
    }
    public function show(Order $order) {
        $order->load('items.variant.product','addresses','payments','shipments');
        return view('admin/orders_show', compact('order'));
    }
    public function update(Order $order) {
        $order->update(['status'=>request('status','pending')]);
        return back()->with('ok','Updated');
    }
}
