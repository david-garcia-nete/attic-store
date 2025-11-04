@extends('layout')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Admin Dashboard</h1>
<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
  <div class="p-4 bg-white rounded shadow">Today's Orders: {{ $todaysOrders }}</div>
  <div class="p-4 bg-white rounded shadow">Today's Revenue: ${{ number_format($revenue,2) }}</div>
  <div class="p-4 bg-white rounded shadow">Unfulfilled: {{ $unfulfilled }}</div>
  <div class="p-4 bg-white rounded shadow">Low Stock: {{ $lowStock->count() }}</div>
</div>
@endsection
