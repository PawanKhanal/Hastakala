<?php

namespace App\Http\Livewire;

use App\Mail\OrderReceived;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\InvoiceService;
use Livewire\Component;
use Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class Checkout extends Component
{
    public function khaltiCheckout(){
        $amount = 1000; // convert the amount to paisa as the amount should be in paisa for khalti
$purchase_order_id = 778877;
$purchase_order_name = "dragon";
$name = "Pawan";
$email = "kb@gmail.com";
$phone = 9869837027;


$postFields = array(
    "return_url" => "http://localhost/shopping/verify_khalti_payment.php",
    "website_url" => "http://localhost/khalti/",
    "amount" => $amount,
    "purchase_order_id" => $purchase_order_id,
    "purchase_order_name" => $purchase_order_name,
    "customer_info" => array(
        "name" => $name,
        "email" => $email,
        "phone" => $phone
    )
);

$jsonData = json_encode($postFields);

$curl = curl_init();
curl_setopt_array($curl, array(
CURLOPT_URL => 'https://a.khalti.com/api/v2/epayment/initiate/',
CURLOPT_RETURNTRANSFER => true,
CURLOPT_ENCODING => '',
CURLOPT_MAXREDIRS => 10,
CURLOPT_TIMEOUT => 0,
CURLOPT_FOLLOWLOCATION => true,
CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
CURLOPT_CUSTOMREQUEST => 'POST',
CURLOPT_POSTFIELDS => $jsonData,
CURLOPT_HTTPHEADER => array(
    'Authorization: key 04dc9a4f23fb436387f2e2aee1b72b94',
    'Content-Type: application/json',
),
));

$response = curl_exec($curl);


if (curl_errno($curl)) {
echo 'Error:' . curl_error($curl);
} else {
$responseArray = json_decode($response, true);

if (isset($responseArray['error'])) {
    echo 'Error: ' . $responseArray['error'];
} elseif (isset($responseArray['payment_url'])) {
    // Redirect the user to the payment page
    header('Location: ' . $responseArray['payment_url']);
} else {
    echo 'Unexpected response: ' . $response;
}
}

curl_close($curl);
    }
    public function success(Request $request, InvoiceService $invoiceService)
    {
        $paymentId = $request->get('payment_id');
        $order = Order::where('session_id', $paymentId)->first();

        if ($order && $order->status === 'pending') {
            $order->status = 'processing';
            $order->save();
            Mail::to($order->user->email)->send(new OrderReceived($order, $invoiceService->createInvoice($order)));
            Cart::destroy();
            return view('livewire.success', compact('order'));
        } else {
            return redirect()->route('home')->with('error', 'Order not found or already processed.');
        }
    }

    // Handle cancel payment response
    public function cancel()
    {
        return redirect()->route('home')->with('error', 'Your order has been canceled.');
    }

    // Render the checkout page
    public function render()
    {
        // Ensure the cart is not empty
        if (Cart::count() <= 0) {
            session()->flash('error', 'Your cart is empty.');
            return redirect()->route('home');
        }

        $user = Auth::user();
        $billingDetails = $user->billingDetails;
        return view('livewire.checkout', compact('billingDetails'));
    }
}
