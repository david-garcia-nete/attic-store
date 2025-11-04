@extends('layout')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Products</h1>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
  @foreach($products as $p)
  <div class="p-4 bg-white rounded shadow">
    <h2 class="font-semibold mb-2"><a href="/product/{{ $p->slug }}">{{ $p->name }}</a></h2>
    <p class="text-sm text-gray-600 mb-2">${{ number_format($p->price,2) }}</p>
    <a class="inline-block px-3 py-2 bg-black text-white rounded" href="/product/{{ $p->slug }}">View</a>
  </div>
  @endforeach
</div>
@if(method_exists($products,'links'))
  <div class="mt-4">{{ $products->links() }}</div>
@endif
@endsection
