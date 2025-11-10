<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Attic Store</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
  <header class="border-b bg-white">
    <div class="max-w-6xl mx-auto px-6 py-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <div class="flex items-center justify-between gap-6">
        <a href="{{ route('home') }}" class="text-xl font-bold tracking-tight">Attic Store</a>
        <nav class="flex items-center gap-4 text-sm font-medium text-gray-600">
          <a class="hover:text-gray-900" href="{{ route('products.index') }}">Shop</a>
          <a class="hover:text-gray-900" href="{{ route('cart.view') }}">Cart</a>
          <a class="hover:text-gray-900" href="{{ route('admin.dashboard') }}">Admin</a>
        </nav>
      </div>
      <form method="get" action="{{ route('products.index') }}" class="flex w-full md:w-80">
        <label for="site-search" class="sr-only">Search products</label>
        <input id="site-search" name="q" value="{{ request('q') }}" class="w-full rounded-l border border-gray-300 px-3 py-2 text-sm focus:border-black focus:outline-none" placeholder="Search for vinyl, apparel, and more">
        <button class="rounded-r bg-black px-4 text-sm font-semibold text-white">Search</button>
      </form>
    </div>
  </header>
  <main class="max-w-6xl mx-auto p-6">
    @if(session('ok'))
      <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('ok') }}</div>
    @endif
    @if($errors->any())
      <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <p class="font-semibold">We couldn't complete that action:</p>
        <ul class="list-disc pl-5">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif
    @yield('content')
  </main>
</body>
</html>
