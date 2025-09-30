<?php

namespace App\Services\PaymentGateways;

use App\Services\PaymentGateways\Contracts\PaymentGatewayInterface;
use App\Services\PaymentGateways\Gateways\StripeGateway;
use App\Services\PaymentGateways\Gateways\DummyGateway;

class PaymentGatewayFactory
{
    /**
     * Available gateways
     */
    private static array $gateways = [
        'stripe' => StripeGateway::class,
        'dummy' => DummyGateway::class,
        // Aquí agregarás más gateways:
        // 'pagarme' => PagarMeGateway::class,
        // 'mercadopago' => MercadoPagoGateway::class,
    ];

    /**
     * Create gateway instance
     * 
     * @param string|null $gateway Gateway name, if null uses default from config
     * @return PaymentGatewayInterface
     * @throws \Exception
     */
    public static function create(?string $gateway = null): PaymentGatewayInterface
    {
        $gateway = $gateway ?? config('payment_gateways.default', 'dummy');

        if (!isset(self::$gateways[$gateway])) {
            throw new \Exception("Gateway '{$gateway}' not found");
        }

        $gatewayClass = self::$gateways[$gateway];
        $instance = new $gatewayClass();

        if (!$instance->isAvailable()) {
            throw new \Exception("Gateway '{$gateway}' is not properly configured");
        }

        return $instance;
    }

    /**
     * Get all available gateways
     * 
     * @return array
     */
    public static function getAvailableGateways(): array
    {
        $available = [];

        foreach (self::$gateways as $name => $class) {
            try {
                $instance = new $class();
                if ($instance->isAvailable()) {
                    $available[] = [
                        'name' => $name,
                        'display_name' => ucfirst($name),
                        'class' => $class,
                    ];
                }
            } catch (\Exception $e) {
                // Skip unavailable gateways
                continue;
            }
        }

        return $available;
    }

    /**
     * Check if gateway exists
     * 
     * @param string $gateway
     * @return bool
     */
    public static function exists(string $gateway): bool
    {
        return isset(self::$gateways[$gateway]);
    }

    /**
     * Register a new gateway
     * 
     * @param string $name
     * @param string $class
     * @return void
     */
    public static function register(string $name, string $class): void
    {
        self::$gateways[$name] = $class;
    }
}
