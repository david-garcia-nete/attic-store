@extends('layout')
@section('content')
<div class="flex justify-between items-center mb-3">
  <h1 class="text-2xl font-semibold">Products</h1>
  <a href="{{ route('admin.products.create') }}" class="rounded bg-black px-3 py-2 text-white">New</a>
</div>
<table class="w-full bg-white rounded shadow">
  <tr class="text-left"><th class="p-2">Name</th><th class="p-2">Price</th><th class="p-2">Actions</th></tr>
  @foreach($products as $p)
  <tr class="border-t">
    <td class="p-2">{{ $p->name }}</td>
    <td class="p-2">${{ number_format($p->price,2) }}</td>
    <td class="p-2"><a class="text-blue-600" href="{{ route('admin.products.edit',$p) }}">Edit</a></td>
  </tr>
  @endforeach
</table>
<div class="mt-3">{{ $products->links() }}</div>
@endsection
