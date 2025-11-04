@extends('layout')
@section('content')
<h1 class="text-2xl font-semibold mb-3">Order {{ $order->number }}</h1>
<p>Status: {{ $order->status }}</p>
<h2 class="text-xl font-semibold mt-4 mb-2">Items</h2>
<table class="w-full bg-white rounded shadow">
  <tr class="text-left"><th class="p-2">Item</th><th class="p-2">Qty</th><th class="p-2">Bin</th><th class="p-2">Line Total</th></tr>
  @foreach($order->items as $i)
  <tr class="border-t">
    <td class="p-2">{{ $i->variant->product->name }} ({{ $i->variant->sku }})</td>
    <td class="p-2">{{ $i->quantity }}</td>
    <td class="p-2">{{ $i->bin_snapshot }}</td>
    <td class="p-2">${{ number_format($i->line_total,2) }}</td>
  </tr>
  @endforeach
</table>

<form method="post" action="{{ route('admin.orders.fulfill',$order) }}" class="mt-4">
  @csrf
  <button class="px-4 py-2 bg-black text-white rounded">Mark Fulfilled</button>
</form>
@endsection
