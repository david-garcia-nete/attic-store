@extends('layout')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Edit Product</h1>
<p class="text-gray-600 mb-6">Editing: <strong>{{ $product->name }}</strong></p>

<div class="grid gap-8 lg:grid-cols-[2fr,1fr]">
  <div class="space-y-6">
    <form method="post" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
      @csrf
      @method('patch')
      <div>
        <label class="mb-2 block text-sm font-semibold text-gray-700" for="name">Product name</label>
        <input id="name" name="name" value="{{ old('name', $product->name) }}" required class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none">
      </div>
      <div class="grid gap-4 md:grid-cols-2">
        <div>
          <label class="mb-2 block text-sm font-semibold text-gray-700" for="slug">Slug</label>
          <input id="slug" name="slug" value="{{ old('slug', $product->slug) }}" required class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none">
        </div>
        <div>
          <label class="mb-2 block text-sm font-semibold text-gray-700" for="price">Base price</label>
          <input id="price" name="price" value="{{ old('price', $product->price) }}" type="number" step="0.01" min="0" required class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none">
        </div>
      </div>
      <div>
        <label class="mb-2 block text-sm font-semibold text-gray-700" for="description">Description</label>
        <textarea id="description" name="description" rows="5" class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none">{{ old('description', $product->description) }}</textarea>
      </div>
      <div>
        <label class="mb-2 block text-sm font-semibold text-gray-700">Categories</label>
        <div class="flex flex-wrap gap-3">
          @forelse($categories as $category)
            @php $checked = in_array($category->id, old('category_ids', $product->categories->pluck('id')->all())); @endphp
            <label class="flex items-center gap-2 rounded border border-gray-200 px-3 py-2 text-sm text-gray-700">
              <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" {{ $checked ? 'checked' : '' }}>
              {{ $category->name }}
            </label>
          @empty
            <p class="text-sm text-gray-500">No categories yet.</p>
          @endforelse
        </div>
      </div>
      <div class="flex items-center gap-3">
        <input id="is_active" type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
        <label for="is_active" class="text-sm text-gray-700">Visible on storefront</label>
      </div>
      <div>
        <label class="mb-2 block text-sm font-semibold text-gray-700" for="image">Primary image</label>
        <input id="image" type="file" name="image" class="block w-full text-sm text-gray-600">
        <p class="mt-1 text-xs text-gray-500">Uploading a new image replaces the current one.</p>
        @if(method_exists($product, 'getFirstMediaUrl') && $product->getFirstMediaUrl())
          <img src="{{ $product->getFirstMediaUrl() }}" alt="{{ $product->name }}" class="mt-3 h-40 w-40 rounded object-cover">
        @endif
      </div>
      <div class="flex items-center gap-3">
        <button class="rounded bg-black px-5 py-2 text-sm font-semibold text-white">Save changes</button>
        <a href="{{ route('admin.products.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Back to list</a>
      </div>
    </form>

    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Variants</h2>
        <span class="text-xs uppercase tracking-wide text-gray-500">{{ $product->variants->count() }} total</span>
      </div>
      <div class="mt-4 space-y-4">
        @forelse($product->variants as $variant)
          <div class="space-y-3 rounded border border-gray-200 p-4">
            <form method="post" action="{{ route('admin.variants.update', $variant) }}" class="space-y-3">
              @csrf
              <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">SKU
                <input name="sku" value="{{ $variant->sku }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none">
              </label>
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Option summary
                <input name="option_summary" value="{{ $variant->option_summary }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none">
              </label>
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Price override
                <input name="price" type="number" step="0.01" value="{{ $variant->price }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none">
              </label>
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Qty on hand
                <input name="qty_on_hand" type="number" value="{{ optional($variant->inventory)->qty_on_hand }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none">
              </label>
              </div>
              <div class="grid gap-3 md:grid-cols-3">
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Weight (oz)
                <input name="weight_oz" type="number" step="0.1" value="{{ $variant->weight_oz }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none">
              </label>
              <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Bin location
                <input name="bin_location" value="{{ optional($variant->inventory)->bin_location }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none">
              </label>
              <label class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Active?
                <input type="checkbox" name="is_active" value="1" {{ $variant->is_active ? 'checked' : '' }}>
              </label>
              </div>
              <div class="flex items-center justify-between">
                <button class="rounded bg-black px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white">Update variant</button>
                <span class="text-xs text-gray-500">Reserved: {{ optional($variant->inventory)->qty_reserved ?? 0 }}</span>
              </div>
            </form>
            <form method="post" action="{{ route('admin.variants.destroy', $variant) }}" onsubmit="return confirm('Delete this variant?')" class="text-right">
              @csrf
              @method('delete')
              <button class="text-xs font-semibold uppercase tracking-wide text-red-600">Delete variant</button>
            </form>
          </div>
        @empty
          <p class="text-sm text-gray-600">No variants yet. Create one below.</p>
        @endforelse
      </div>

      <div class="mt-6 border-t border-gray-200 pt-6">
        <h3 class="text-sm font-semibold text-gray-900">Add a new variant</h3>
        <form method="post" action="{{ route('admin.variants.store') }}" class="mt-3 space-y-3 rounded border border-dashed border-gray-300 p-4">
          @csrf
          <input type="hidden" name="product_id" value="{{ $product->id }}">
          <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">SKU
              <input name="sku" required class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none">
            </label>
            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Option summary
              <input name="option_summary" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none">
            </label>
            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Price override
              <input name="price" type="number" step="0.01" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none">
            </label>
            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Qty on hand
              <input name="qty_on_hand" type="number" value="0" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none">
            </label>
          </div>
          <div class="grid gap-3 md:grid-cols-3">
            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Weight (oz)
              <input name="weight_oz" type="number" step="0.1" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none">
            </label>
            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Length (in)
              <input name="length_in" type="number" step="0.1" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none">
            </label>
            <label class="text-xs font-semibold uppercase tracking-wide text-gray-500">Bin location
              <input name="bin_location" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none">
            </label>
          </div>
          <button class="rounded bg-black px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white">Add variant</button>
        </form>
      </div>
    </section>
  </div>

  <aside class="space-y-4">
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
      <h2 class="text-lg font-semibold text-gray-900">Quick stats</h2>
      <dl class="mt-4 space-y-2 text-sm text-gray-700">
        <div class="flex items-center justify-between">
          <dt>Created</dt>
          <dd>{{ $product->created_at->format('M d, Y') }}</dd>
        </div>
        <div class="flex items-center justify-between">
          <dt>Updated</dt>
          <dd>{{ $product->updated_at->format('M d, Y') }}</dd>
        </div>
        <div class="flex items-center justify-between">
          <dt>Variants</dt>
          <dd>{{ $product->variants->count() }}</dd>
        </div>
      </dl>
    </div>
    <form method="post" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete this product? This cannot be undone.')" class="rounded-xl border border-red-200 bg-red-50 p-6 text-sm text-red-700">
      @csrf
      @method('delete')
      <p class="font-semibold">Danger zone</p>
      <p class="mt-2">Deleting the product removes all variants and media.</p>
      <button class="mt-3 rounded bg-red-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white">Delete product</button>
    </form>
  </aside>
</div>
@endsection
