<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\WebhookLog;
use App\Services\PaymentGateways\PaymentGatewayFactory;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle Stripe webhooks
     */
    public function stripe(Request $request)
    {
        return $this->handleWebhook($request, 'stripe');
    }

    /**
     * Handle Pagar.me webhooks
     */
    public function pagarme(Request $request)
    {
        return $this->handleWebhook($request, 'pagarme');
    }

    /**
     * Handle Mercado Pago webhooks
     */
    public function mercadopago(Request $request)
    {
        return $this->handleWebhook($request, 'mercadopago');
    }

    /**
     * Handle Dummy gateway webhooks (for testing)
     */
    public function dummy(Request $request)
    {
        return $this->handleWebhook($request, 'dummy');
    }

    /**
     * Generic webhook handler
     */
    private function handleWebhook(Request $request, string $gatewayName)
    {
        try {
            // Log incoming webhook
            $webhookLog = $this->logWebhook($request, $gatewayName);

            // Get gateway instance
            $gateway = PaymentGatewayFactory::create($gatewayName);

            // Verify webhook signature (if applicable)
            $signature = $request->header('X-Signature') ?? $request->header('Stripe-Signature');
            
            if ($signature && !$gateway->verifyWebhookSignature($request->getContent(), $signature)) {
                Log::warning("Invalid webhook signature for {$gatewayName}");
                
                $webhookLog->markAsFailed('Invalid signature');
                
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            // Process webhook payload
            $payload = $request->all();
            $processedData = $gateway->processWebhook($payload);

            // Handle different event types
            $result = $this->handleWebhookEvent($processedData, $webhookLog);

            if ($result['success']) {
                $webhookLog->markAsProcessed($result['message']);
                
                return response()->json(['success' => true], 200);
            }

            $webhookLog->markAsFailed($result['message']);
            
            return response()->json(['error' => $result['message']], 400);

        } catch (\Exception $e) {
            Log::error("Webhook processing error for {$gatewayName}", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (isset($webhookLog)) {
                $webhookLog->markAsFailed($e->getMessage());
            }

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Log webhook request
     */
    private function logWebhook(Request $request, string $gateway): WebhookLog
    {
        return WebhookLog::create([
            'gateway' => $gateway,
            'event_type' => $request->input('type') ?? $request->input('event') ?? 'unknown',
            'event_id' => $request->input('id'),
            'payload' => json_encode($request->all()),
            'headers' => $request->headers->all(),
            'status' => 'pending',
        ]);
    }

    /**
     * Handle specific webhook events
     */
    private function handleWebhookEvent(array $data, WebhookLog $webhookLog): array
    {
        $eventType = $data['event_type'] ?? 'unknown';

        switch ($eventType) {
            case 'payment.succeeded':
            case 'payment.paid':
                return $this->handlePaymentSucceeded($data, $webhookLog);

            case 'payment.failed':
                return $this->handlePaymentFailed($data, $webhookLog);

            case 'payment.refunded':
                return $this->handlePaymentRefunded($data, $webhookLog);

            case 'subscription.updated':
            case 'subscription.renewed':
                return $this->handleSubscriptionUpdated($data, $webhookLog);

            case 'subscription.cancelled':
                return $this->handleSubscriptionCancelled($data, $webhookLog);

            case 'subscription.payment_failed':
                return $this->handleSubscriptionPaymentFailed($data, $webhookLog);

            default:
                return [
                    'success' => true,
                    'message' => 'Event type not handled: ' . $eventType,
                ];
        }
    }

    /**
     * Handle payment succeeded event
     */
    private function handlePaymentSucceeded(array $data, WebhookLog $webhookLog): array
    {
        $transactionId = $data['transaction_id'] ?? null;
        
        if (!$transactionId) {
            return ['success' => false, 'message' => 'Missing transaction_id'];
        }

        $order = Order::where('gateway_transaction_id', $transactionId)->first();

        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }

        if (!$order->isPaid()) {
            $order->markAsPaid();
            
            // Activate subscription if exists
            if ($order->subscription) {
                $order->subscription->activate();
            }
        }

        $webhookLog->update(['order_id' => $order->id]);

        return ['success' => true, 'message' => 'Payment marked as paid'];
    }

    /**
     * Handle payment failed event
     */
    private function handlePaymentFailed(array $data, WebhookLog $webhookLog): array
    {
        $transactionId = $data['transaction_id'] ?? null;
        
        if (!$transactionId) {
            return ['success' => false, 'message' => 'Missing transaction_id'];
        }

        $order = Order::where('gateway_transaction_id', $transactionId)->first();

        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }

        $order->markAsFailed();
        
        $webhookLog->update(['order_id' => $order->id]);

        return ['success' => true, 'message' => 'Payment marked as failed'];
    }

    /**
     * Handle payment refunded event
     */
    private function handlePaymentRefunded(array $data, WebhookLog $webhookLog): array
    {
        $transactionId = $data['transaction_id'] ?? null;
        
        if (!$transactionId) {
            return ['success' => false, 'message' => 'Missing transaction_id'];
        }

        $order = Order::where('gateway_transaction_id', $transactionId)->first();

        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }

        $order->update([
            'payment_status' => 'refunded',
            'refunded_at' => now(),
        ]);
        
        $webhookLog->update(['order_id' => $order->id]);

        return ['success' => true, 'message' => 'Payment marked as refunded'];
    }

    /**
     * Handle subscription updated event
     */
    private function handleSubscriptionUpdated(array $data, WebhookLog $webhookLog): array
    {
        $subscriptionId = $data['gateway_subscription_id'] ?? null;
        
        if (!$subscriptionId) {
            return ['success' => false, 'message' => 'Missing subscription_id'];
        }

        $subscription = Subscription::where('gateway_subscription_id', $subscriptionId)->first();

        if (!$subscription) {
            return ['success' => false, 'message' => 'Subscription not found'];
        }

        // Update subscription details from webhook data
        if (isset($data['data']['current_period_end'])) {
            $subscription->update([
                'current_period_end' => $data['data']['current_period_end'],
                'status' => $data['data']['status'] ?? $subscription->status,
            ]);
        }

        $webhookLog->update(['subscription_id' => $subscription->id]);

        return ['success' => true, 'message' => 'Subscription updated'];
    }

    /**
     * Handle subscription cancelled event
     */
    private function handleSubscriptionCancelled(array $data, WebhookLog $webhookLog): array
    {
        $subscriptionId = $data['gateway_subscription_id'] ?? null;
        
        if (!$subscriptionId) {
            return ['success' => false, 'message' => 'Missing subscription_id'];
        }

        $subscription = Subscription::where('gateway_subscription_id', $subscriptionId)->first();

        if (!$subscription) {
            return ['success' => false, 'message' => 'Subscription not found'];
        }

        $subscription->cancel();
        
        $webhookLog->update(['subscription_id' => $subscription->id]);

        return ['success' => true, 'message' => 'Subscription cancelled'];
    }

    /**
     * Handle subscription payment failed event
     */
    private function handleSubscriptionPaymentFailed(array $data, WebhookLog $webhookLog): array
    {
        $subscriptionId = $data['gateway_subscription_id'] ?? null;
        
        if (!$subscriptionId) {
            return ['success' => false, 'message' => 'Missing subscription_id'];
        }

        $subscription = Subscription::where('gateway_subscription_id', $subscriptionId)->first();

        if (!$subscription) {
            return ['success' => false, 'message' => 'Subscription not found'];
        }

        $subscription->incrementFailedAttempts();

        // Suspend subscription after max failed attempts
        $maxAttempts = config('payment_gateways.subscription.max_failed_attempts', 3);
        
        if ($subscription->failed_payment_attempts >= $maxAttempts) {
            $subscription->suspend();
        }
        
        $webhookLog->update(['subscription_id' => $subscription->id]);

        return ['success' => true, 'message' => 'Subscription payment failure recorded'];
    }
}