<?php
/*web.php segun DeepSeek V3.2*/

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

// Ruta de inicio
Route::get('/', function () {
    return view('welcome');
});

// Rutas de checkout
Route::get('/checkout/{product?}', [CheckoutController::class, 'show'])->name('checkout.show'); //ej. /checkout/premium
Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/error', [CheckoutController::class, 'error'])->name('checkout.error');

// Rutas de PIX
Route::get('/checkout/pix/waiting/{order}', [CheckoutController::class, 'pixWaiting'])->name('checkout.pix.waiting');
Route::get('/checkout/pix/status/{order}', [CheckoutController::class, 'checkPixStatus'])->name('checkout.pix.status');

// Rutas de suscripciones
Route::prefix('subscriptions')->group(function () {
    Route::get('/{subscription}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::post('/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
    Route::post('/{subscription}/resume', [SubscriptionController::class, 'resume'])->name('subscriptions.resume');
    Route::post('/{subscription}/payment-method', [SubscriptionController::class, 'updatePaymentMethod'])->name('subscriptions.payment-method.update');
    Route::get('/{subscription}/status', [SubscriptionController::class, 'status'])->name('subscriptions.status');
    Route::get('/{subscription}/invoices', [SubscriptionController::class, 'invoices'])->name('subscriptions.invoices');
    Route::get('/{subscription}/invoices/{invoiceId}/download', [SubscriptionController::class, 'downloadInvoice'])->name('subscriptions.invoices.download');
});

// Rutas de webhooks
Route::prefix('webhooks')->group(function () {
    Route::post('/pagarme', [WebhookController::class, 'pagarme'])->name('webhooks.pagarme');
    Route::post('/stripe', [WebhookController::class, 'stripe'])->name('webhooks.stripe');
    Route::post('/mercadopago', [WebhookController::class, 'mercadopago'])->name('webhooks.mercadopago');
});

// Rutas de demo
Route::get('/demo/checkout', function () {
    return redirect()->route('checkout.show', 'premium-monthly');
});

Route::get('/demo/products', function () {
    $products = [
        [
            'name' => 'Plano Premium Mensal',
            'slug' => 'premium-monthly',
            'price' => 'R$ 97,00',
            'description' => 'Acesso completo por 1 mês',
            'url' => route('checkout.show', 'premium-monthly')
        ],
        // ... otros productos
    ];

    return view('demo.products', compact('products'));
})->name('demo.products');

// Ruta de health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'environment' => app()->environment(),
    ]);
});