@extends('layout')
@section('content')
<section class="mb-10 grid gap-6 lg:grid-cols-[2fr,1fr]">
  <div class="rounded-xl bg-gradient-to-r from-stone-900 via-stone-800 to-stone-700 px-8 py-10 text-white shadow-xl">
    <p class="text-sm uppercase tracking-[0.4em] text-stone-300">Curated finds</p>
    <h1 class="mt-3 text-3xl font-semibold md:text-4xl">{{ $isLanding ? 'Discover rare pressings & handmade goods' : 'Shop the Attic collection' }}</h1>
    <p class="mt-4 max-w-xl text-stone-200">
      From small-batch apparel to limited vinyl runs, we source pieces that bring character to every corner of your home.
    </p>
    <div class="mt-6 flex flex-wrap gap-3">
      <a href="{{ route('products.index') }}" class="rounded bg-white px-5 py-2 text-sm font-semibold text-stone-900 shadow">Browse all products</a>
      <a href="{{ route('cart.view') }}" class="rounded border border-white/40 px-5 py-2 text-sm font-semibold text-white hover:bg-white/10">View cart</a>
    </div>
  </div>
  <div class="rounded-xl border border-dashed border-gray-300 bg-white/40 p-6 backdrop-blur">
    <h2 class="text-lg font-semibold text-gray-800">Why shop Attic?</h2>
    <ul class="mt-4 space-y-3 text-sm text-gray-600">
      <li class="flex items-start gap-2"><span class="mt-1 h-2 w-2 rounded-full bg-green-500"></span> Independent brands vetted by crate-diggers and makers.</li>
      <li class="flex items-start gap-2"><span class="mt-1 h-2 w-2 rounded-full bg-green-500"></span> Fast, trackable shipping on every order.</li>
      <li class="flex items-start gap-2"><span class="mt-1 h-2 w-2 rounded-full bg-green-500"></span> Rewards and seasonal drops for loyal customers.</li>
    </ul>
  </div>
</section>

@if(!$isLanding && ($searchTerm || $activeCategory))
  <div class="mb-6 text-sm text-gray-600">
    @if($searchTerm)
      <span>Showing results for <strong>“{{ $searchTerm }}”</strong>.</span>
    @endif
    @if($activeCategory)
      <span>Filtered by <strong>{{ $activeCategory->name }}</strong>.</span>
    @endif
  </div>
@endif

@if($categories->isNotEmpty())
  <div class="mb-8 flex flex-wrap gap-3">
    <a href="{{ route('products.index') }}" class="rounded-full border px-4 py-2 text-sm {{ $activeCategory ? 'border-gray-300 text-gray-600 hover:border-black hover:text-black' : 'border-black bg-black text-white' }}">All</a>
    @foreach($categories as $category)
      <a href="{{ route('products.index', array_merge(request()->except('page'), ['category' => $category->slug])) }}" class="rounded-full border px-4 py-2 text-sm {{ optional($activeCategory)->id === $category->id ? 'border-black bg-black text-white' : 'border-gray-300 text-gray-600 hover:border-black hover:text-black' }}">{{ $category->name }}</a>
    @endforeach
  </div>
@endif

@if($products->isEmpty())
  <div class="rounded-lg border border-dashed border-gray-300 bg-white p-6 text-center text-gray-600">
    <p class="font-medium">We couldn’t find any products that match your filters.</p>
    <a href="{{ route('products.index') }}" class="mt-3 inline-block rounded bg-black px-4 py-2 text-sm font-semibold text-white">Clear filters</a>
  </div>
@else
  <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
    @foreach($products as $p)
      @include('components.storefront.product-card', ['product' => $p])
    @endforeach
  </div>
  @if(method_exists($products,'links'))
    <div class="mt-6">{{ $products->links() }}</div>
  @endif
@endif
@endsection
