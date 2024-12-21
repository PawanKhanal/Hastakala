@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Payment Page</h2>

    <div class="order-summary">
        <h4>Your Order</h4>
        <ul>
            @foreach($order['products'] as $product)
                <li>{{ $product['name'] }} x {{ $product['quantity'] }} - NPR {{ $product['price'] * $product['quantity'] }}</li>
            @endforeach
        </ul>
        <p><strong>Total:</strong> NPR {{ $order['total'] }}</p>
    </div>

    <div id="payment-button-container">
        <button id="khalti-button" class="btn btn-success">Pay with Khalti</button>
    </div>
</div>
<script src="https://khalti.com/static/khalti-checkout.js"></script>
<script>
    var config = {
        "publicKey": "9d48f08403264bfba1e701123e2e66d8",
        "productIdentity": "{{ $order['products'][0]['id'] ?? 'product' }}",
        "productName": "{{ $order['products'][0]['name'] ?? 'Product' }}",
        "productUrl": "{{ url('/') }}",
        "eventHandler": {
            onSuccess(payload) {
                axios.post('{{ route('payment.verify') }}', {
                    token: payload.token,
                    amount: payload.amount
                })
                .then(response => {
                    window.location.href = '{{ route('checkout.success') }}';
                })
                .catch(error => {
                    alert('Payment verification failed!');
                });
            },
            onError(error) {
                console.log(error);
            }
        }
    };

    var checkout = new KhaltiCheckout(config);

    document.getElementById("payment-button").onclick = function() {
        checkout.show({amount: {{ $order['total'] * 100 }} }); // Khalti amount is in paisa
    }
</script>
