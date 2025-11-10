@extends('layout')
@section('content')
<h1 class="text-3xl font-semibold mb-6">Checkout</h1>

<div class="grid gap-8 lg:grid-cols-[2fr,1fr]">
  <form id="checkout-form" method="post" action="{{ route('checkout.stripe') }}" data-quote-endpoint="{{ route('checkout.shipQuote') }}" data-weight="{{ $totals['total_weight_oz'] }}" class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    @csrf
    <div>
      <h2 class="text-lg font-semibold text-gray-900">Shipping address</h2>
      <p class="text-sm text-gray-500">We’ll only use this information to deliver your order.</p>
    </div>
    <div class="grid gap-4">
      <input class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none" name="ship_name" placeholder="Full name" required>
      <input class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none" name="ship_line1" placeholder="Address line 1" required>
      <input class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none" name="ship_line2" placeholder="Address line 2 (optional)">
      <div class="grid gap-3 md:grid-cols-3">
        <input class="rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none" name="ship_city" placeholder="City" required>
        <input class="rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none" name="ship_state" placeholder="State" required>
        <input id="ship_postal" class="rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none" name="ship_postal" placeholder="ZIP / Postal" required>
      </div>
      <input class="rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none" name="ship_phone" placeholder="Contact phone (optional)">
    </div>

    <div class="rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-700">
      <p class="font-semibold text-gray-900">Estimated shipping</p>
      <p class="mt-1">Based on your cart weight ({{ $totals['total_weight_oz'] }} oz), shipping starts at <span id="shipping-quote-value">${{ number_format($totals['shipping_total'],2) }}</span>.</p>
    </div>

    <div class="flex flex-col gap-3 md:flex-row">
      <button type="submit" class="flex-1 rounded bg-black px-5 py-3 text-sm font-semibold uppercase tracking-wide text-white">Pay with Stripe</button>
      <button type="submit" formaction="{{ route('checkout.paypal') }}" class="flex-1 rounded bg-blue-600 px-5 py-3 text-sm font-semibold uppercase tracking-wide text-white">Pay with PayPal</button>
    </div>
    <p class="text-xs text-gray-500">By clicking pay you agree to our terms of service and authorize Attic Store to process your payment.</p>
  </form>

  <aside class="space-y-4">
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
      <h2 class="text-lg font-semibold text-gray-900">Order summary</h2>
      <ul class="mt-4 space-y-3 text-sm text-gray-700">
        @foreach($cart->items as $item)
          <li class="flex items-start justify-between gap-3">
            <div>
              <p class="font-semibold text-gray-900">{{ $item->variant->product->name }}</p>
              <p class="text-xs text-gray-500">{{ $item->variant->sku }} · Qty {{ $item->quantity }}</p>
            </div>
            <span class="font-semibold">${{ number_format($item->unit_price * $item->quantity,2) }}</span>
          </li>
        @endforeach
      </ul>
      <dl class="mt-6 space-y-3 text-sm text-gray-700">
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
          <dt>Total due today</dt>
          <dd>${{ number_format($totals['grand_total'],2) }}</dd>
        </div>
      </dl>
    </div>
    <div class="rounded-xl border border-dashed border-gray-300 bg-white p-5 text-sm text-gray-600">
      <p class="font-semibold text-gray-800">Need to make changes?</p>
      <p class="mt-1">You can head back to your <a href="{{ route('cart.view') }}" class="text-gray-900 underline">cart</a> to update quantities or remove items before confirming payment.</p>
    </div>
  </aside>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('checkout-form');
  if (!form) return;
  const postal = document.getElementById('ship_postal');
  const quoteEl = document.getElementById('shipping-quote-value');
  const endpoint = form.dataset.quoteEndpoint;
  const token = form.querySelector('input[name="_token"]').value;
  const weight = form.dataset.weight;

  const requestQuote = () => {
    if (!endpoint) return;
    fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({ weight_oz: weight, postal: postal?.value })
    })
      .then(response => response.ok ? response.json() : Promise.reject())
      .then(data => {
        if (data.rate && quoteEl) {
          quoteEl.textContent = `$${Number(data.rate).toFixed(2)}`;
        }
      })
      .catch(() => {});
  };

  requestQuote();
  postal?.addEventListener('change', requestQuote);
});
</script>
@endsection
