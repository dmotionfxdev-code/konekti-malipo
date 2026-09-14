<?php

return [
    'default' => env('PAYMENT_GATEWAY', 'pesapal'),
    'pending_expiry_minutes' => (int) env('PAYMENT_PENDING_EXPIRY_MINUTES', 30),
    'gateways' => [
        'pesapal' => [
            'enabled' => (bool) env('PESAPAL_ENABLED', true),
            'environment' => env('PESAPAL_ENVIRONMENT', 'sandbox'),
            'base_url' => env('PESAPAL_BASE_URL', env('PESAPAL_ENVIRONMENT', 'sandbox') === 'production' ? 'https://pay.pesapal.com/v3/api' : 'https://cybqa.pesapal.com/pesapalv3/api'),
            'consumer_key' => env('PESAPAL_CONSUMER_KEY'), 'consumer_secret' => env('PESAPAL_CONSUMER_SECRET'),
            'ipn_id' => env('PESAPAL_IPN_ID'), 'ipn_url' => env('PESAPAL_IPN_URL'), 'timeout' => env('PESAPAL_TIMEOUT', 15),
            'currencies' => ['TZS', 'KES', 'UGX', 'USD', 'EUR', 'GBP'],
        ],
        'azampesa' => ['enabled' => (bool) env('AZAMPESA_ENABLED', false)],
        'flutterwave' => ['enabled' => (bool) env('FLUTTERWAVE_ENABLED', false)],
    ],
];
