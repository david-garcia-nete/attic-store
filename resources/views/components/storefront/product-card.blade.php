@php
    $primaryImage = method_exists($product, 'getFirstMediaUrl') ? $product->getFirstMediaUrl() : null;
    $variantPrices = $product->variants->map(fn($variant) => $variant->priceEffective());
    $price = $variantPrices->isNotEmpty() ? $variantPrices->min() : $product->price;
@endphp
<div class="group flex h-full flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
  <a href="{{ route('products.show', $product->slug) }}" class="relative block aspect-square overflow-hidden bg-gray-100">
    @if($primaryImage)
      <img src="{{ $primaryImage }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
    @else
      <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-gray-200 via-gray-100 to-gray-200 text-sm font-semibold text-gray-500">No image yet</div>
    @endif
  </a>
  <div class="flex flex-1 flex-col px-5 py-4">
    <div class="flex items-start justify-between gap-3">
      <h3 class="text-base font-semibold text-gray-900">
        <a href="{{ route('products.show', $product->slug) }}" class="hover:text-black">{{ $product->name }}</a>
      </h3>
      <span class="shrink-0 rounded bg-stone-900 px-2 py-1 text-xs font-semibold text-white">${{ number_format($price, 2) }}</span>
    </div>
    <p class="mt-2 min-h-[3.25rem] text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($product->description, 120) ?: 'No description available yet.' }}</p>
    <div class="mt-auto pt-4">
      <a href="{{ route('products.show', $product->slug) }}" class="inline-flex items-center justify-center rounded border border-black px-4 py-2 text-sm font-semibold text-black transition hover:bg-black hover:text-white">View details</a>
    </div>
  </div>
</div>
