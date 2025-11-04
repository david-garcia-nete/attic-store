<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Order};

class OrderFulfillmentController extends Controller
{
    public function fulfill(Order $order) {
        foreach ($order->items as $item) {
            $inv = $item->variant->inventory()->lockForUpdate()->first();
            if ($inv) { $inv->commit($item->quantity); }
        }
        $order->update(['status'=>'fulfilled']);
        return back()->with('ok','Order fulfilled');
    }
}
