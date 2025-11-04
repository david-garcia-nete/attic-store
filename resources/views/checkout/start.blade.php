@extends('layout')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Checkout</h1>
<form method="post" action="/checkout/pay/stripe" class="space-y-2">
  @csrf
  <h2 class="font-semibold">Shipping Address</h2>
  <input class="border p-2 rounded w-full" name="ship_name" placeholder="Name">
  <input class="border p-2 rounded w-full" name="ship_line1" placeholder="Address line 1">
  <input class="border p-2 rounded w-full" name="ship_line2" placeholder="Address line 2">
  <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
    <input class="border p-2 rounded" name="ship_city" placeholder="City">
    <input class="border p-2 rounded" name="ship_state" placeholder="State">
    <input class="border p-2 rounded" name="ship_postal" placeholder="ZIP">
  </div>
  <button class="px-4 py-2 bg-black text-white rounded">Pay with Stripe</button>
</form>
<form method="post" action="/checkout/pay/paypal" class="mt-4">
  @csrf
  <button class="px-4 py-2 bg-blue-600 text-white rounded">Pay with PayPal</button>
</form>
@endsection
