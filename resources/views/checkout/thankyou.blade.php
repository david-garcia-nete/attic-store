@extends('layout')
@section('content')
<h1 class="text-2xl font-semibold mb-2">Thank you!</h1>
<p>Your order {{ $order->number }} has been received.</p>
@endsection
