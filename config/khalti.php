<?php 
return [
    'api_key' => env('KHALTI_API_KEY'),
    'secret_key' => env('KHALTI_SECRET_KEY'),
    'sandbox' => env('KHALTI_SANDBOX', true),
    'base_url' => env('KHALTI_SANDBOX') ? 'https://sandbox.khalti.com/api/v2/charge/' : 'https://khalti.com/api/v2/charge/',
];

