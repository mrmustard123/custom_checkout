<?php

/*
File: AbstractPaymentGateway
Author: Leonardo G. Tellez Saucedo
Created on: 29 sep. de 2025 17:38:22
Email: leonardo616@gmail.com
*/

namespace App\Services\PaymentGateways;

use App\Services\PaymentGateways\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;

abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    protected string $apiKey;
    protected string $apiSecret;
    protected bool $sandbox;
    protected string $baseUrl;

    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Initialize gateway configuration
     */
    abstract protected function initialize(): void;

    /**
     * Log gateway activity
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        Log::channel('payment_gateways')->{$level}(
            "[{$this->getGatewayName()}] {$message}",
            $context
        );
    }

    /**
     * Validate card data
     */
    protected function validateCardData(array $cardData): bool
    {
        $required = ['number', 'holder_name', 'exp_month', 'exp_year', 'cvv'];
        
        foreach ($required as $field) {
            if (!isset($cardData[$field]) || empty($cardData[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }

        // Validate card number (Luhn algorithm)
        if (!$this->isValidCardNumber($cardData['number'])) {
            throw new \InvalidArgumentException("Invalid card number");
        }

        // Validate expiration
        $expMonth = (int) $cardData['exp_month'];
        $expYear = (int) $cardData['exp_year'];
        
        if ($expMonth < 1 || $expMonth > 12) {
            throw new \InvalidArgumentException("Invalid expiration month");
        }

        $expDate = \Carbon\Carbon::createFromDate($expYear, $expMonth, 1)->endOfMonth();
        if (now()->isAfter($expDate)) {
            throw new \InvalidArgumentException("Card is expired");
        }

        return true;
    }

    /**
     * Luhn algorithm for card validation
     */
    protected function isValidCardNumber(string $number): bool
    {
        $number = preg_replace('/\D/', '', $number);
        
        if (strlen($number) < 13 || strlen($number) > 19) {
            return false;
        }

        $sum = 0;
        $numDigits = strlen($number);
        $parity = $numDigits % 2;

        for ($i = 0; $i < $numDigits; $i++) {
            $digit = (int) $number[$i];
            
            if ($i % 2 == $parity) {
                $digit *= 2;
            }
            
            if ($digit > 9) {
                $digit -= 9;
            }
            
            $sum += $digit;
        }

        return ($sum % 10) == 0;
    }

    /**
     * Get card brand from number
     */
    protected function getCardBrand(string $number): string
    {
        $number = preg_replace('/\D/', '', $number);
        
        $patterns = [
            'visa' => '/^4/',
            'mastercard' => '/^(5[1-5]|2[2-7])/',
            'amex' => '/^3[47]/',
            'discover' => '/^6(?:011|5)/',
            'diners' => '/^3(?:0[0-5]|[68])/',
            'jcb' => '/^35/',
            'elo' => '/^(4011|4312|4389|4514|4576|5041|5066|5090|6277|6362|6363|6516|6550)/',
        ];

        foreach ($patterns as $brand => $pattern) {
            if (preg_match($pattern, $number)) {
                return $brand;
            }
        }

        return 'unknown';
    }

    /**
     * Format amount to cents
     */
    protected function formatAmountToCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * Format amount from cents
     */
    protected function formatAmountFromCents(int $cents): float
    {
        return $cents / 100;
    }

    /**
     * Make HTTP request
     */
    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        try {
            $url = $this->baseUrl . $endpoint;
            
            $this->log('info', "Making {$method} request to {$endpoint}", [
                'data' => $data,
            ]);

            $response = \Illuminate\Support\Facades\Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->{strtolower($method)}($url, $data);

            if ($response->successful()) {
                $this->log('info', "Request successful", [
                    'status' => $response->status(),
                ]);
                
                return $response->json();
            }

            $this->log('error', "Request failed", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \Exception("Gateway request failed: " . $response->body());

        } catch (\Exception $e) {
            $this->log('error', "Request exception", [
                'message' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Get request headers
     */
    abstract protected function getHeaders(): array;

    /**
     * Handle gateway exception
     */
    protected function handleException(\Exception $e, string $context = ''): array
    {
        $this->log('error', "Exception in {$context}", [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return [
            'success' => false,
            'message' => $e->getMessage(),
            'error' => true,
        ];
    }

    /**
     * Check if gateway is in sandbox mode
     */
    public function isSandbox(): bool
    {
        return $this->sandbox;
    }
}