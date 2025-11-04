@extends('layout')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Edit Product</h1>
<form method="post" action="{{ route('products.update',$product) }}" enctype="multipart/form-data" class="space-y-3">
  @csrf @method('put')
  <input name="name" value="{{ $product->name }}" class="border p-2 rounded w-full">
  <input name="slug" value="{{ $product->slug }}" class="border p-2 rounded w-full">
  <input name="price" value="{{ $product->price }}" class="border p-2 rounded w-full">
  <textarea name="description" class="border p-2 rounded w-full">{{ $product->description }}</textarea>
  <label class="block"><input type="checkbox" name="is_active" value="1" {{ $product->is_active ? 'checked' : '' }}> Active</label>
  <input type="file" name="image">
  <button class="px-4 py-2 bg-black text-white rounded">Save</button>
</form>

<h2 class="text-xl font-semibold mt-6 mb-2">Variants</h2>
<form method="post" action="{{ route('variants.store') }}" class="grid grid-cols-1 md:grid-cols-6 gap-2 bg-white p-3 rounded shadow">
  @csrf
  <input type="hidden" name="product_id" value="{{ $product->id }}">
  <input name="sku" placeholder="SKU" class="border p-2 rounded">
  <input name="price" placeholder="Price" class="border p-2 rounded">
  <input name="qty_on_hand" placeholder="Qty" class="border p-2 rounded">
  <input name="bin_location" placeholder="Bin" class="border p-2 rounded">
  <button class="px-3 py-2 bg-black text-white rounded">Add Variant</button>
</form>

<table class="w-full mt-3 bg-white rounded shadow">
  <tr class="text-left"><th class="p-2">SKU</th><th class="p-2">Price</th><th class="p-2">Qty</th><th class="p-2">Bin</th></tr>
  @foreach($product->variants as $v)
    <tr class="border-t">
      <td class="p-2">{{ $v->sku }}</td>
      <td class="p-2">{{ $v->price ?? $product->price }}</td>
      <td class="p-2">{{ optional($v->inventory)->qty_on_hand ?? 0 }}</td>
      <td class="p-2">{{ optional($v->inventory)->bin_location }}</td>
    </tr>
  @endforeach
</table>
@endsection
