@extends('layout')
@section('content')
<h1 class="text-2xl font-semibold mb-4">{{ $product->name }}</h1>
<p class="mb-4 text-gray-700">{{ $product->description }}</p>
<form method="post" action="/cart/add" class="space-y-3">
  @csrf
  <label class="block">Variant</label>
  <select name="variant_id" class="border p-2 rounded">
    @foreach($product->variants as $v)
      <option value="{{ $v->id }}">{{ $v->sku }} — ${{ number_format($v->priceEffective(),2) }} ({{ optional($v->inventory)->qty_on_hand ?? 0 }} in stock)</option>
    @endforeach
  </select>
  <input type="number" name="quantity" value="1" min="1" class="border p-2 rounded w-24">
  <button class="px-4 py-2 bg-black text-white rounded">Add to cart</button>
</form>
@endsection
