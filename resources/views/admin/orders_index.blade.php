@extends('layout')
@section('content')
<h1 class="text-2xl font-semibold mb-3">Orders</h1>
<table class="w-full bg-white rounded shadow">
  <tr class="text-left"><th class="p-2">#</th><th class="p-2">Status</th><th class="p-2">Total</th><th class="p-2">Placed</th><th class="p-2">Actions</th></tr>
  @foreach($orders as $o)
  <tr class="border-t">
    <td class="p-2">{{ $o->number }}</td>
    <td class="p-2">{{ $o->status }}</td>
    <td class="p-2">${{ number_format($o->grand_total,2) }}</td>
    <td class="p-2">{{ $o->created_at->format('Y-m-d H:i') }}</td>
    <td class="p-2"><a class="text-blue-600" href="{{ route('admin.orders.show',$o) }}">View</a></td>
  </tr>
  @endforeach
</table>
<div class="mt-3">{{ $orders->links() }}</div>
@endsection
