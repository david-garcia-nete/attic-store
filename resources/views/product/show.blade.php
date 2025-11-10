@extends('layout')
@section('content')
<div class="grid gap-10 lg:grid-cols-[1.2fr,1fr]">
  <div class="space-y-6">
    <div class="aspect-square overflow-hidden rounded-xl border border-gray-200 bg-gray-100">
      @if(method_exists($product, 'getFirstMediaUrl') && $product->getFirstMediaUrl())
        <img src="{{ $product->getFirstMediaUrl() }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
      @else
        <div class="flex h-full items-center justify-center text-gray-400">Product imagery coming soon</div>
      @endif
    </div>
    <div>
      <h2 class="text-lg font-semibold text-gray-800">Details</h2>
      <p class="mt-3 whitespace-pre-line text-gray-700">{{ $product->description ?: 'We are gathering the story behind this product. Check back shortly!' }}</p>
    </div>
  </div>
  <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
    <a href="{{ route('products.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to catalog</a>
    <h1 class="mt-2 text-3xl font-semibold text-gray-900">{{ $product->name }}</h1>
    <p class="mt-2 text-sm uppercase tracking-[0.25em] text-gray-400">Attic Store Exclusive</p>
    <div class="mt-5 space-y-5">
      @if($product->variants->isEmpty())
        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-600">
          <p class="font-semibold text-gray-800">Currently sold out</p>
          <p class="mt-1">This product doesn't have any active variants available. Check back soon or reach out to our team for restock updates.</p>
        </div>
      @else
        <form method="post" action="{{ route('cart.add') }}" class="space-y-4">
          @csrf
          <input type="hidden" name="product_id" value="{{ $product->id }}">
          <div>
            <label for="variant_id" class="mb-2 block text-sm font-semibold text-gray-700">Choose a variant</label>
            <select id="variant_id" name="variant_id" class="w-full rounded border border-gray-300 px-3 py-2 focus:border-black focus:outline-none">
              @foreach($product->variants as $v)
                @php
                  $inStock = optional($v->inventory)->qty_on_hand - optional($v->inventory)->qty_reserved;
                @endphp
                <option value="{{ $v->id }}" @selected((string)old('variant_id') === (string)$v->id)>{{ $v->option_summary ?: $v->sku }} — ${{ number_format($v->priceEffective(),2) }} ({{ max($inStock ?? 0, 0) }} available)</option>
              @endforeach
            </select>
          </div>
          <div>
            <label for="quantity" class="mb-2 block text-sm font-semibold text-gray-700">Quantity</label>
            <input id="quantity" type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1" class="w-24 rounded border border-gray-300 px-3 py-2 focus:border-black focus:outline-none">
          </div>
          <button class="w-full rounded bg-black px-4 py-3 text-sm font-semibold uppercase tracking-wide text-white">Add to cart</button>
        </form>
      @endif
      <div class="rounded-lg bg-gray-50 p-4 text-sm text-gray-600">
        <p class="font-semibold text-gray-800">Shipping & pickup</p>
        <p class="mt-1">Ships within 2 business days from our Portland studio. Local pickup is available — add a note at checkout and we'll reach out.</p>
      </div>
    </div>
  </div>
</div>
@endsection
