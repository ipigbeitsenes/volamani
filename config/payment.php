<?php

return [
    'default' => env('PAYMENT_GATEWAY', 'paystack'),

    'paystack' => [
        'public_key'   => env('PAYSTACK_PUBLIC_KEY', ''),
        'secret_key'   => env('PAYSTACK_SECRET_KEY', ''),
        'callback_url' => env('PAYSTACK_CALLBACK_URL', env('APP_URL') . '/checkout/callback'),
        'base_url'     => 'https://api.paystack.co',
    ],

    'platform_fee_percent' => (int) env('PLATFORM_FEE_PERCENT', 10),

    'bank_transfer' => [
        'bank_name'      => env('BANK_NAME', 'Access Bank'),
        'account_name'   => env('BANK_ACCOUNT_NAME', 'Volamani Technologies Ltd'),
        'account_number' => env('BANK_ACCOUNT_NUMBER', '0123456789'),
    ],
];
