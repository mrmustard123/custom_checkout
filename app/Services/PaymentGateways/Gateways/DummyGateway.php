<?php

namespace App\Services\PaymentGateways\Gateways;

use App\Services\PaymentGateways\AbstractPaymentGateway;
use App\Models\Order;
use App\Models\Subscription;
use Illuminate\Support\Str;

/**
 * Dummy Gateway for testing without real API calls
 * Simulates successful payments
 */
class DummyGateway extends AbstractPaymentGateway
{
    protected function initialize(): void
    {
        $this->apiKey = 'dummy_key';
        $this->apiSecret = 'dummy_secret';
        $this->sandbox = true;
        $this->baseUrl = 'http://dummy-gateway.test';
    }

    public function getGatewayName(): string
    {
        return 'dummy';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    protected function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    public function createPixPayment(Order $order): array
    {
        $this->log('info', 'Creating dummy PIX payment', ['order_id' => $order->id]);

        // Simulate PIX QR Code
        $qrCodeData = base64_encode("PIX|{$order->order_number}|{$order->total_amount}");
        
        return [
            'success' => true,
            'qr_code' => $qrCodeData,
            'qr_code_url' => "https://dummy-gateway.test/qr/{$qrCodeData}",
            'expiration' => now()->addMinutes(30),
            'transaction_id' => 'DUMMY_PIX_' . Str::random(16),
            'message' => 'PIX payment created successfully',
        ];
    }

    public function createCreditCardPayment(Order $order, array $cardData): array
    {
        try {
            $this->validateCardData($cardData);
            
            $this->log('info', 'Creating dummy credit card payment', [
                'order_id' => $order->id,
                'card_brand' => $this->getCardBrand($cardData['number']),
            ]);

            // Simulate payment processing delay
            usleep(500000); // 0.5 seconds

            // Always approve for testing
            return [
                'success' => true,
                'transaction_id' => 'DUMMY_CC_' . Str::random(16),
                'charge_id' => 'DUMMY_CHG_' . Str::random(16),
                'message' => 'Payment processed successfully',
                'card_brand' => $this->getCardBrand($cardData['number']),
                'card_last4' => substr($cardData['number'], -4),
            ];

        } catch (\Exception $e) {
            return $this->handleException($e, 'createCreditCardPayment');
        }
    }

    public function createSubscription(Subscription $subscription, array $cardData): array
    {
        try {
            $this->validateCardData($cardData);
            
            $this->log('info', 'Creating dummy subscription', [
                'subscription_id' => $subscription->id,
            ]);

            // Simulate processing
            usleep(500000);

            return [
                'success' => true,
                'subscription_id' => 'DUMMY_SUB_' . Str::random(16),
                'message' => 'Subscription created successfully',
            ];

        } catch (\Exception $e) {
            return $this->handleException($e, 'createSubscription');
        }
    }

    public function cancelSubscription(Subscription $subscription): array
    {
        $this->log('info', 'Cancelling dummy subscription', [
            'subscription_id' => $subscription->id,
        ]);

        return [
            'success' => true,
            'message' => 'Subscription cancelled successfully',
        ];
    }

    public function getPaymentStatus(string $transactionId): array
    {
        $this->log('info', 'Getting payment status', [
            'transaction_id' => $transactionId,
        ]);

        return [
            'status' => 'paid',
            'paid_at' => now(),
        ];
    }

    public function getSubscriptionStatus(string $subscriptionId): array
    {
        $this->log('info', 'Getting subscription status', [
            'subscription_id' => $subscriptionId,
        ]);

        return [
            'status' => 'active',
            'current_period_end' => now()->addMonth(),
        ];
    }

    public function processWebhook(array $payload): array
    {
        $eventType = $payload['event_type'] ?? 'payment.succeeded';

        return [
            'event_type' => $eventType,
            'order_id' => $payload['order_id'] ?? null,
            'subscription_id' => $payload['subscription_id'] ?? null,
            'transaction_id' => $payload['transaction_id'] ?? null,
            'data' => $payload,
        ];
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        // Always valid for dummy
        return true;
    }

    public function refundPayment(Order $order, ?float $amount = null): array
    {
        $this->log('info', 'Processing dummy refund', [
            'order_id' => $order->id,
            'amount' => $amount ?? $order->total_amount,
        ]);

        return [
            'success' => true,
            'refund_id' => 'DUMMY_REF_' . Str::random(16),
            'message' => 'Refund processed successfully',
        ];
    }
}
