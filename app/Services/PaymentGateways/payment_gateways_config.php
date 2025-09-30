<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | This option controls the default payment gateway that will be used
    | when no specific gateway is specified.
    |
    | Supported: "stripe", "pagarme", "mercadopago", "dummy"
    |
    */

    'default' => env('PAYMENT_GATEWAY_DEFAULT', 'dummy'),

    /*
    |--------------------------------------------------------------------------
    | Stripe Configuration
    |--------------------------------------------------------------------------
    */

    'stripe' => [
        'secret_key' => env('STRIPE_SECRET_KEY', ''),
        'publishable_key' => env('STRIPE_PUBLISHABLE_KEY', ''),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),
        'sandbox' => env('STRIPE_SANDBOX', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagar.me Configuration
    |--------------------------------------------------------------------------
    */

    'pagarme' => [
        'api_key' => env('PAGARME_API_KEY', ''),
        'encryption_key' => env('PAGARME_ENCRYPTION_KEY', ''),
        'webhook_secret' => env('PAGARME_WEBHOOK_SECRET', ''),
        'sandbox' => env('PAGARME_SANDBOX', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Mercado Pago Configuration
    |--------------------------------------------------------------------------
    */

    'mercadopago' => [
        'access_token' => env('MERCADOPAGO_ACCESS_TOKEN', ''),
        'public_key' => env('MERCADOPAGO_PUBLIC_KEY', ''),
        'webhook_secret' => env('MERCADOPAGO_WEBHOOK_SECRET', ''),
        'sandbox' => env('MERCADOPAGO_SANDBOX', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Dummy Gateway Configuration (for testing)
    |--------------------------------------------------------------------------
    */

    'dummy' => [
        'enabled' => env('DUMMY_GATEWAY_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | PIX Configuration
    |--------------------------------------------------------------------------
    */

    'pix' => [
        'expiration_minutes' => env('PIX_EXPIRATION_MINUTES', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Configuration
    |--------------------------------------------------------------------------
    */

    'subscription' => [
        'trial_days' => env('SUBSCRIPTION_TRIAL_DAYS', 0),
        'grace_period_days' => env('SUBSCRIPTION_GRACE_PERIOD_DAYS', 3),
        'max_failed_attempts' => env('SUBSCRIPTION_MAX_FAILED_ATTEMPTS', 3),
    ],

];