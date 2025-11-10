@extends('layout')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Create Product</h1>
<p class="text-gray-600 mb-6">Use this screen to add a new product.</p>

<form method="post" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="space-y-6 max-w-3xl">
  @csrf
  <div>
    <label class="mb-2 block text-sm font-semibold text-gray-700" for="name">Product name</label>
    <input id="name" name="name" value="{{ old('name') }}" required class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none">
  </div>
  <div class="grid gap-4 md:grid-cols-2">
    <div>
      <label class="mb-2 block text-sm font-semibold text-gray-700" for="slug">Slug</label>
      <input id="slug" name="slug" value="{{ old('slug') }}" required class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none">
      <p class="mt-1 text-xs text-gray-500">Used for storefront URLs.</p>
    </div>
    <div>
      <label class="mb-2 block text-sm font-semibold text-gray-700" for="price">Base price</label>
      <input id="price" name="price" value="{{ old('price') }}" type="number" step="0.01" min="0" required class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none">
    </div>
  </div>
  <div>
    <label class="mb-2 block text-sm font-semibold text-gray-700" for="description">Description</label>
    <textarea id="description" name="description" rows="5" class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none">{{ old('description') }}</textarea>
  </div>
  <div>
    <label class="mb-2 block text-sm font-semibold text-gray-700">Categories</label>
    <div class="flex flex-wrap gap-3">
      @forelse($categories as $category)
        <label class="flex items-center gap-2 rounded border border-gray-200 px-3 py-2 text-sm text-gray-700">
          <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" {{ in_array($category->id, old('category_ids', [])) ? 'checked' : '' }}>
          {{ $category->name }}
        </label>
      @empty
        <p class="text-sm text-gray-500">No categories yet. Create some to organize your catalog.</p>
      @endforelse
    </div>
  </div>
  <div class="flex items-center gap-3">
    <input id="is_active" type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
    <label for="is_active" class="text-sm text-gray-700">Visible on storefront</label>
  </div>
  <div>
    <label class="mb-2 block text-sm font-semibold text-gray-700" for="image">Primary image</label>
    <input id="image" type="file" name="image" class="block w-full text-sm text-gray-600">
    <p class="mt-1 text-xs text-gray-500">Optional. JPEG or PNG, up to 2MB.</p>
  </div>
  <div class="flex items-center gap-3">
    <button class="rounded bg-black px-5 py-2 text-sm font-semibold text-white">Create product</button>
    <a href="{{ route('admin.products.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Cancel</a>
  </div>
</form>
@endsection
