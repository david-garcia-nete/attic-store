@extends('layout')
@section('content')
<h1 class="text-2xl font-semibold mb-4">Pay with Stripe</h1>
<div id="payment-element"></div>
<button id="submit" class="mt-4 px-4 py-2 bg-black text-white rounded">Pay</button>
<script src="https://js.stripe.com/v3/"></script>
<script>
  const stripe = Stripe("{{ env('STRIPE_KEY') }}");
  const options = { clientSecret: "{{ $clientSecret }}" };
  const elements = stripe.elements(options);
  const paymentElement = elements.create('payment');
  paymentElement.mount('#payment-element');

  document.getElementById('submit').addEventListener('click', async () => {
    const {error} = await stripe.confirmPayment({
      elements,
      confirmParams: { return_url: "{{ route('checkout.thankyou',['order'=>$order->id]) }}" }
    });
    if (error) alert(error.message);
  });
</script>
@endsection
