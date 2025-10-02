<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\PaymentGateways\PaymentGatewayFactory;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_gateway_instance()
    {
        $gateway = PaymentGatewayFactory::create('dummy');
        
        $this->assertNotNull($gateway);
        $this->assertEquals('dummy', $gateway->getGatewayName());
        $this->assertTrue($gateway->isAvailable());
    }

    public function test_can_get_available_gateways()
    {
        $gateways = PaymentGatewayFactory::getAvailableGateways();
        
        $this->assertIsArray($gateways);
        $this->assertNotEmpty($gateways);
    }

    public function test_can_create_pix_payment()
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'cpf' => '12345678900',
        ]);

        $order = Order::create([
            'customer_id' => $customer->id,
            'product_name' => 'Test Product',
            'product_type' => 'one_time',
            'amount' => 100.00,
            'total_amount' => 100.00,
            'payment_method' => 'pix',
            'gateway' => 'dummy',
            'currency' => 'BRL',
        ]);

        $gateway = PaymentGatewayFactory::create('dummy');
        $result = $gateway->createPixPayment($order);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('qr_code', $result);
        $this->assertArrayHasKey('transaction_id', $result);
    }

    public function test_can_create_credit_card_payment()
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
        ]);

        $order = Order::create([
            'customer_id' => $customer->id,
            'product_name' => 'Test Product',
            'product_type' => 'one_time',
            'amount' => 100.00,
            'total_amount' => 100.00,
            'payment_method' => 'credit_card',
            'gateway' => 'dummy',
            'currency' => 'BRL',
        ]);

        $cardData = [
            'number' => '4111111111111111', // Test Visa card
            'holder_name' => 'Test User',
            'exp_month' => '12',
            'exp_year' => '2025',
            'cvv' => '123',
        ];

        $gateway = PaymentGatewayFactory::create('dummy');
        $result = $gateway->createCreditCardPayment($order, $cardData);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('transaction_id', $result);
    }

    public function test_throws_exception_for_invalid_gateway()
    {
        $this->expectException(\Exception::class);
        PaymentGatewayFactory::create('invalid_gateway');
    }
}
