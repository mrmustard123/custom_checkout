<?php

/*
File: StripeGateway
Author: Leonardo G. Tellez Saucedo
Created on: 29 sep. de 2025 17:38:33
Email: leonardo616@gmail.com
*/



namespace App\Services\PaymentGateways\Gateways;

use App\Services\PaymentGateways\AbstractPaymentGateway;
use App\Models\Order;
use App\Models\Subscription;

class StripeGateway extends AbstractPaymentGateway
{
    protected function initialize(): void
    {
        $this->apiKey = config('payment_gateways.stripe.secret_key');
        $this->apiSecret = config('payment_gateways.stripe.webhook_secret');
        $this->sandbox = config('payment_gateways.stripe.sandbox', true);
        $this->baseUrl = $this->sandbox 
            ? 'https://api.stripe.com/v1' 
            : 'https://api.stripe.com/v1';
    }

    public function getGatewayName(): string
    {
        return 'stripe';
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }

    protected function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
    }

    public function createPixPayment(Order $order): array
    {
        // Stripe doesn't support PIX natively, would need to use another method
        // or a Brazilian gateway
        throw new \Exception("PIX is not supported by Stripe. Please use a Brazilian gateway.");
    }

    public function createCreditCardPayment(Order $order, array $cardData): array
    {
        try {
            $this->validateCardData($cardData);

            // Create payment method
            $paymentMethod = $this->makeRequest('POST', '/payment_methods', [
                'type' => 'card',
                'card' => [
                    'number' => $cardData['number'],
                    'exp_month' => $cardData['exp_month'],
                    'exp_year' => $cardData['exp_year'],
                    'cvc' => $cardData['cvv'],
                ],
                'billing_details' => [
                    'name' => $cardData['holder_name'],
                    'email' => $order->customer->email,
                ],
            ]);

            // Create payment intent
            $paymentIntent = $this->makeRequest('POST', '/payment_intents', [
                'amount' => $this->formatAmountToCents($order->total_amount),
                'currency' => strtolower($order->currency),
                'payment_method' => $paymentMethod['id'],
                'confirm' => true,
                'description' => $order->product_name,
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ],
            ]);

            return [
                'success' => $paymentIntent['status'] === 'succeeded',
                'transaction_id' => $paymentIntent['id'],
                'message' => 'Payment processed successfully',
                'charge_id' => $paymentIntent['charges']['data'][0]['id'] ?? null,
            ];

        } catch (\Exception $e) {
            return $this->handleException($e, 'createCreditCardPayment');
        }
    }

    public function createSubscription(Subscription $subscription, array $cardData): array
    {
        try {
            $this->validateCardData($cardData);
            $customer = $subscription->customer;

            // Create or get Stripe customer
            $stripeCustomer = $this->createStripeCustomer($customer);

            // Create payment method
            $paymentMethod = $this->makeRequest('POST', '/payment_methods', [
                'type' => 'card',
                'card' => [
                    'number' => $cardData['number'],
                    'exp_month' => $cardData['exp_month'],
                    'exp_year' => $cardData['exp_year'],
                    'cvc' => $cardData['cvv'],
                ],
            ]);

            // Attach payment method to customer
            $this->makeRequest('POST', "/payment_methods/{$paymentMethod['id']}/attach", [
                'customer' => $stripeCustomer['id'],
            ]);

            // Create subscription
            $interval = $subscription->plan_interval === 'monthly' ? 'month' : 'year';
            
            $stripeSubscription = $this->makeRequest('POST', '/subscriptions', [
                'customer' => $stripeCustomer['id'],
                'items' => [
                    [
                        'price_data' => [
                            'currency' => strtolower($subscription->currency),
                            'product_data' => [
                                'name' => $subscription->plan_name,
                            ],
                            'recurring' => [
                                'interval' => $interval,
                            ],
                            'unit_amount' => $this->formatAmountToCents($subscription->plan_amount),
                        ],
                    ],
                ],
                'default_payment_method' => $paymentMethod['id'],
                'metadata' => [
                    'subscription_id' => $subscription->id,
                ],
            ]);

            return [
                'success' => true,
                'subscription_id' => $stripeSubscription['id'],
                'message' => 'Subscription created successfully',
            ];

        } catch (\Exception $e) {
            return $this->handleException($e, 'createSubscription');
        }
    }

    public function cancelSubscription(Subscription $subscription): array
    {
        try {
            $stripeSubscription = $this->makeRequest(
                'DELETE', 
                "/subscriptions/{$subscription->gateway_subscription_id}"
            );

            return [
                'success' => true,
                'message' => 'Subscription cancelled successfully',
            ];

        } catch (\Exception $e) {
            return $this->handleException($e, 'cancelSubscription');
        }
    }

    public function getPaymentStatus(string $transactionId): array
    {
        try {
            $paymentIntent = $this->makeRequest('GET', "/payment_intents/{$transactionId}");

            return [
                'status' => $this->mapStripeStatus($paymentIntent['status']),
                'paid_at' => $paymentIntent['status'] === 'succeeded' 
                    ? now() 
                    : null,
            ];

        } catch (\Exception $e) {
            return $this->handleException($e, 'getPaymentStatus');
        }
    }

    public function getSubscriptionStatus(string $subscriptionId): array
    {
        try {
            $subscription = $this->makeRequest('GET', "/subscriptions/{$subscriptionId}");

            return [
                'status' => $subscription['status'],
                'current_period_end' => \Carbon\Carbon::createFromTimestamp($subscription['current_period_end']),
            ];

        } catch (\Exception $e) {
            return $this->handleException($e, 'getSubscriptionStatus');
        }
    }

    public function processWebhook(array $payload): array
    {
        $eventType = $payload['type'] ?? null;
        $data = $payload['data']['object'] ?? [];

        switch ($eventType) {
            case 'payment_intent.succeeded':
                return [
                    'event_type' => 'payment.succeeded',
                    'order_id' => $data['metadata']['order_id'] ?? null,
                    'transaction_id' => $data['id'],
                    'data' => $data,
                ];

            case 'payment_intent.payment_failed':
                return [
                    'event_type' => 'payment.failed',
                    'order_id' => $data['metadata']['order_id'] ?? null,
                    'transaction_id' => $data['id'],
                    'data' => $data,
                ];

            case 'customer.subscription.updated':
            case 'customer.subscription.deleted':
                return [
                    'event_type' => 'subscription.updated',
                    'subscription_id' => $data['metadata']['subscription_id'] ?? null,
                    'gateway_subscription_id' => $data['id'],
                    'data' => $data,
                ];

            default:
                return [
                    'event_type' => 'unknown',
                    'data' => $payload,
                ];
        }
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        try {
            $expectedSignature = hash_hmac('sha256', $payload, $this->apiSecret);
            return hash_equals($expectedSignature, $signature);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function refundPayment(Order $order, ?float $amount = null): array
    {
        try {
            $refundData = [
                'payment_intent' => $order->gateway_transaction_id,
            ];

            if ($amount) {
                $refundData['amount'] = $this->formatAmountToCents($amount);
            }

            $refund = $this->makeRequest('POST', '/refunds', $refundData);

            return [
                'success' => true,
                'refund_id' => $refund['id'],
                'message' => 'Refund processed successfully',
            ];

        } catch (\Exception $e) {
            return $this->handleException($e, 'refundPayment');
        }
    }

    private function createStripeCustomer($customer): array
    {
        return $this->makeRequest('POST', '/customers', [
            'name' => $customer->name,
            'email' => $customer->email,
            'metadata' => [
                'customer_id' => $customer->id,
            ],
        ]);
    }

    private function mapStripeStatus(string $status): string
    {
        $mapping = [
            'succeeded' => 'paid',
            'processing' => 'processing',
            'requires_payment_method' => 'pending',
            'requires_confirmation' => 'pending',
            'requires_action' => 'pending',
            'canceled' => 'cancelled',
        ];

        return $mapping[$status] ?? 'pending';
    }
}