@extends('layout')
@section('content')
<h1 class="text-2xl font-semibold mb-6">Your Cart</h1>

@if($cart->items->isEmpty())
  <div class="rounded-lg border border-dashed border-gray-300 bg-white p-6 text-center text-gray-600">
    <p class="font-medium">Your cart is empty right now.</p>
    <a href="{{ route('products.index') }}" class="mt-3 inline-block rounded bg-black px-4 py-2 text-sm font-semibold text-white">Start shopping</a>
  </div>
@else
  <div class="grid gap-8 lg:grid-cols-[2fr,1fr]">
    <div class="space-y-4">
      <table class="w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
          <tr>
            <th class="px-4 py-3">Item</th>
            <th class="px-4 py-3">Qty</th>
            <th class="px-4 py-3">Price</th>
            <th class="px-4 py-3">Total</th>
            <th class="px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody class="text-sm text-gray-700">
          @foreach($cart->items as $item)
            <tr class="border-t border-gray-200">
              <td class="px-4 py-4">
                <div class="font-semibold text-gray-900">{{ $item->variant->product->name }}</div>
                <div class="text-xs text-gray-500">{{ $item->variant->sku }} {{ $item->variant->option_summary ? '· '.$item->variant->option_summary : '' }}</div>
              </td>
              <td class="px-4 py-4">{{ $item->quantity }}</td>
              <td class="px-4 py-4">${{ number_format($item->unit_price,2) }}</td>
              <td class="px-4 py-4">${{ number_format($item->unit_price * $item->quantity,2) }}</td>
              <td class="px-4 py-4">
                <form method="post" action="{{ route('cart.remove', $item) }}" onsubmit="return confirm('Remove this item?')">
                  @csrf
                  @method('delete')
                  <button class="text-xs font-semibold uppercase tracking-wide text-red-600 hover:text-red-500">Remove</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>

      <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Have a discount code?</h2>
        <form method="post" action="{{ route('cart.discount') }}" class="mt-3 flex flex-col gap-3 md:flex-row md:items-center">
          @csrf
          <input name="code" value="{{ old('code') }}" placeholder="Enter code" class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none">
          <button class="rounded bg-black px-4 py-2 text-sm font-semibold uppercase tracking-wide text-white">Apply</button>
        </form>
        @if($totals['discount_code'])
          <div class="mt-3 flex items-center justify-between rounded border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800">
            <span>{{ $totals['discount_label'] ?? ('Code '.$totals['discount_code'].' applied') }}</span>
            <form method="post" action="{{ route('cart.discount') }}">
              @csrf
              <input type="hidden" name="remove" value="1">
              <button class="text-xs font-semibold uppercase tracking-wide text-green-900 hover:underline">Remove</button>
            </form>
          </div>
        @endif
      </div>
    </div>
    <aside class="space-y-4">
      <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900">Order summary</h2>
        <dl class="mt-4 space-y-3 text-sm text-gray-700">
          <div class="flex items-center justify-between">
            <dt>Subtotal</dt>
            <dd>${{ number_format($totals['subtotal'],2) }}</dd>
          </div>
          @if($totals['discount_total'] > 0)
            <div class="flex items-center justify-between text-green-600">
              <dt>Discount</dt>
              <dd>-${{ number_format($totals['discount_total'],2) }}</dd>
            </div>
          @endif
          <div class="flex items-center justify-between">
            <dt>Shipping</dt>
            <dd>${{ number_format($totals['shipping_total'],2) }}</dd>
          </div>
          <div class="flex items-center justify-between">
            <dt>Estimated tax</dt>
            <dd>${{ number_format($totals['tax_total'],2) }}</dd>
          </div>
          <div class="flex items-center justify-between text-base font-semibold text-gray-900">
            <dt>Total</dt>
            <dd>${{ number_format($totals['grand_total'],2) }}</dd>
          </div>
        </dl>
        <a class="mt-6 block rounded bg-black px-4 py-3 text-center text-sm font-semibold uppercase tracking-wide text-white" href="{{ route('checkout.start') }}">Proceed to checkout</a>
        <p class="mt-2 text-xs text-gray-500">Cart weight: {{ $totals['total_weight_oz'] }} oz</p>
      </div>
      <div class="rounded-xl border border-dashed border-gray-300 bg-white p-5 text-sm text-gray-600">
        <p class="font-semibold text-gray-800">Need a shipping estimate?</p>
        <p class="mt-1">You can preview real-time rates on the next step. Free shipping kicks in automatically on orders over $150.</p>
      </div>
    </aside>
  </div>
@endif
@endsection
