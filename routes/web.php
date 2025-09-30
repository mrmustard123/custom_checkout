<?php

use Illuminate\Support\Facades\Route;
//use app\Services\Checkout

/*Route::get('/', function () {
    return view('welcome');
});*/


// Checkout
Route::get('/checkout/{product?}', [CheckoutController::class, 'show']);
Route::post('/checkout/process', [CheckoutController::class, 'process']);

// Webhooks (sin CSRF)
Route::post('/webhook/pagarme', [WebhookController::class, 'pagarme']);
Route::post('/webhook/stripe', [WebhookController::class, 'stripe']);

// Success/Error pages
Route::get('/success/{order}', [CheckoutController::class, 'success']);
Route::get('/error', [CheckoutController::class, 'error']);