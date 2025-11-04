<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Attic Store</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
  <header class="p-4 bg-white shadow flex justify-between">
    <a href="/" class="font-bold">Attic Store</a>
    <nav>
      <a class="px-3" href="/products">Products</a>
      <a class="px-3" href="/cart">Cart</a>
      <a class="px-3" href="/admin">Admin</a>
    </nav>
  </header>
  <main class="max-w-5xl mx-auto p-6">
    @if(session('ok'))<div class="p-3 bg-green-100 text-green-800 mb-4">{{ session('ok') }}</div>@endif
    @yield('content')
  </main>
</body>
</html>
