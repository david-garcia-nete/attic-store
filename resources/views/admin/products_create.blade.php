@extends('layout')
@section('content')
<h1 class="text-2xl font-semibold mb-4">New Product</h1>
<form method="post" action="{{ route('products.store') }}" enctype="multipart/form-data" class="space-y-3">
  @csrf
  <input name="name" class="border p-2 rounded w-full" placeholder="Name">
  <input name="slug" class="border p-2 rounded w-full" placeholder="slug">
  <input name="price" class="border p-2 rounded w-full" placeholder="price">
  <textarea name="description" class="border p-2 rounded w-full" placeholder="description"></textarea>
  <input type="file" name="image">
  <button class="px-4 py-2 bg-black text-white rounded">Create</button>
</form>
@endsection
