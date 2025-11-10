@extends('layout')
@section('content')
<div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
  <h1 class="text-3xl font-semibold text-gray-900">Thank you!</h1>
  <p class="mt-2 text-sm text-gray-600">Your order <span class="font-semibold text-gray-900">#{{ $order->number }}</span> is confirmed. We’ve sent a receipt to your email.</p>

  <h2 class="mt-6 text-lg font-semibold text-gray-900">What happens next?</h2>
  <p class="mt-2 text-sm text-gray-600">Our fulfillment team is packing your items. You’ll receive a tracking number as soon as it ships.</p>

  <div class="mt-6 overflow-hidden rounded-lg border border-gray-200">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
      <thead class="bg-gray-50">
        <tr class="text-left">
          <th class="px-4 py-3 font-semibold text-gray-600">Item</th>
          <th class="px-4 py-3 font-semibold text-gray-600">Qty</th>
          <th class="px-4 py-3 font-semibold text-gray-600">Line total</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        @foreach($order->items as $item)
          <tr>
            <td class="px-4 py-3">
              <div class="font-semibold text-gray-900">{{ $item->variant->product->name }}</div>
              <div class="text-xs text-gray-500">{{ $item->variant->sku }}</div>
            </td>
            <td class="px-4 py-3">{{ $item->quantity }}</td>
            <td class="px-4 py-3">${{ number_format($item->line_total,2) }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <dl class="mt-6 grid gap-3 text-sm text-gray-700 md:grid-cols-2 lg:grid-cols-4">
    <div class="rounded border border-gray-200 bg-gray-50 p-3">
      <dt class="text-xs uppercase tracking-wide text-gray-500">Subtotal</dt>
      <dd class="text-base font-semibold text-gray-900">${{ number_format($order->subtotal,2) }}</dd>
    </div>
    <div class="rounded border border-gray-200 bg-gray-50 p-3">
      <dt class="text-xs uppercase tracking-wide text-gray-500">Shipping</dt>
      <dd class="text-base font-semibold text-gray-900">${{ number_format($order->shipping_total,2) }}</dd>
    </div>
    <div class="rounded border border-gray-200 bg-gray-50 p-3">
      <dt class="text-xs uppercase tracking-wide text-gray-500">Tax</dt>
      <dd class="text-base font-semibold text-gray-900">${{ number_format($order->tax_total,2) }}</dd>
    </div>
    <div class="rounded border border-gray-200 bg-gray-50 p-3">
      <dt class="text-xs uppercase tracking-wide text-gray-500">Total paid</dt>
      <dd class="text-base font-semibold text-gray-900">${{ number_format($order->grand_total,2) }}</dd>
    </div>
  </dl>

  <div class="mt-6 flex flex-col gap-3 text-sm text-gray-600 md:flex-row md:items-center md:justify-between">
    <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center rounded bg-black px-5 py-2 text-sm font-semibold text-white">Keep shopping</a>
    <p>Questions? Reply to your confirmation email and our team will help.</p>
  </div>
</div>
@endsection
