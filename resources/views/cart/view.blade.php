@extends('layout')
@section('content')
<h1 class="text-2xl font-semibold mb-3">Your Cart</h1>
<table class="w-full mb-4 bg-white shadow rounded">
  <tr class="text-left">
    <th class="p-2">Item</th><th class="p-2">Qty</th><th class="p-2">Price</th><th class="p-2">Total</th>
  </tr>
  @foreach($cart->items as $i)
    <tr class="border-t">
      <td class="p-2">{{ $i->variant->product->name }} ({{ $i->variant->sku }})</td>
      <td class="p-2">{{ $i->quantity }}</td>
      <td class="p-2">${{ number_format($i->unit_price,2) }}</td>
      <td class="p-2">${{ number_format($i->unit_price * $i->quantity,2) }}</td>
    </tr>
  @endforeach
</table>
<div class="p-4 bg-white rounded shadow">
  <p>Subtotal: ${{ number_format($totals['subtotal'],2) }}</p>
  <p>Shipping: ${{ number_format($totals['shipping_total'],2) }}</p>
  <p>Tax: ${{ number_format($totals['tax_total'],2) }}</p>
  <p class="font-semibold">Grand Total: ${{ number_format($totals['grand_total'],2) }}</p>
  <div class="mt-3">
    <a class="px-4 py-2 bg-black text-white rounded" href="/checkout">Checkout</a>
  </div>
</div>
@endsection
