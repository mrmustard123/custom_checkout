<?php

/*
File: PaymentGatewayInterface
Author: Leonardo G. Tellez Saucedo
Created on: 29 sep. de 2025 17:38:02
Email: leonardo616@gmail.com
*/


namespace App\Services\PaymentGateways\Contracts;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Subscription;

interface PaymentGatewayInterface
{
    /**
     * Create a PIX payment
     * 
     * @param Order $order
     * @return array Contains: qr_code, qr_code_url, expiration, transaction_id
     */
    public function createPixPayment(Order $order): array;

    /**
     * Create a credit card payment
     * 
     * @param Order $order
     * @param array $cardData Contains: number, holder_name, exp_month, exp_year, cvv
     * @return array Contains: success, transaction_id, message
     */
    public function createCreditCardPayment(Order $order, array $cardData): array;

    /**
     * Create a subscription
     * 
     * @param Subscription $subscription
     * @param array $cardData
     * @return array Contains: success, subscription_id, message
     */
    public function createSubscription(Subscription $subscription, array $cardData): array;

    /**
     * Cancel a subscription
     * 
     * @param Subscription $subscription
     * @return array Contains: success, message
     */
    public function cancelSubscription(Subscription $subscription): array;

    /**
     * Get payment status
     * 
     * @param string $transactionId
     * @return array Contains: status, paid_at, etc
     */
    public function getPaymentStatus(string $transactionId): array;

    /**
     * Get subscription status
     * 
     * @param string $subscriptionId
     * @return array Contains: status, current_period_end, etc
     */
    public function getSubscriptionStatus(string $subscriptionId): array;

    /**
     * Process webhook payload
     * 
     * @param array $payload
     * @return array Contains: event_type, order_id, subscription_id, data
     */
    public function processWebhook(array $payload): array;

    /**
     * Verify webhook signature
     * 
     * @param string $payload
     * @param string $signature
     * @return bool
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool;

    /**
     * Refund a payment
     * 
     * @param Order $order
     * @param float|null $amount Null for full refund
     * @return array Contains: success, refund_id, message
     */
    public function refundPayment(Order $order, ?float $amount = null): array;

    /**
     * Get gateway name
     * 
     * @return string
     */
    public function getGatewayName(): string;

    /**
     * Check if gateway is available
     * 
     * @return bool
     */
    public function isAvailable(): bool;
}