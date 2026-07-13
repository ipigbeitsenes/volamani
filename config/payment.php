<?php

return [
    'default' => env('PAYMENT_GATEWAY', 'paystack'),

    'paystack' => [
        'public_key' => env('PAYSTACK_PUBLIC_KEY', ''),
        'secret_key' => env('PAYSTACK_SECRET_KEY', ''),
        'callback_url' => env('PAYSTACK_CALLBACK_URL', env('APP_URL').'/checkout/callback'),
        'base_url' => 'https://api.paystack.co',
    ],

    'flutterwave' => [
        'public_key' => env('FLUTTERWAVE_PUBLIC_KEY', ''),
        'secret_key' => env('FLUTTERWAVE_SECRET_KEY', ''),
        'secret_hash' => env('FLUTTERWAVE_SECRET_HASH', ''),   // webhook "verif-hash"
        'callback_url' => env('FLUTTERWAVE_CALLBACK_URL', env('APP_URL').'/checkout/callback'),
        'base_url' => 'https://api.flutterwave.com/v3',
    ],

    'platform_fee_percent' => (int) env('PLATFORM_FEE_PERCENT', 10),

    // Fee deducted from a vendor withdrawal (percent of the gross amount).
    'withdrawal_fee_percent' => (float) env('WITHDRAWAL_FEE_PERCENT', 1.5),

    // Paid promotion: a vendor pays this flat fee (kobo) from their wallet to
    // feature a product for `days` days.
    'promotion' => [
        'fee' => (int) env('PROMOTION_FEE_KOBO', 1_000_00), // ₦1,000
        'days' => (int) env('PROMOTION_DAYS', 7),
    ],

    'bank_transfer' => [
        'bank_name' => env('BANK_NAME', ''),
        'account_name' => env('BANK_ACCOUNT_NAME', ''),
        'account_number' => env('BANK_ACCOUNT_NUMBER', ''),
    ],
];
