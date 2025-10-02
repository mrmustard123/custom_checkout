<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\PaymentMethod;
use App\Services\PaymentGateways\PaymentGatewayFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends Controller
{
    /**
     * Show checkout page
     */
    public function show(Request $request, ?string $product = null)
    {
        // Get available products/plans (hardcoded for now, later from DB)
        $products = $this->getAvailableProducts();
        
        // Get selected product or default
        $selectedProduct = $product ? ($products[$product] ?? $products['premium']) : $products['premium'];
        
        // Get available gateways
        $availableGateways = PaymentGatewayFactory::getAvailableGateways();
        
        return view('checkout.show', [
            'product' => $selectedProduct,
            'products' => $products,
            'gateways' => $availableGateways,
        ]);
    }

    /**
     * Process checkout payment
     */
    public function process(Request $request)
    {
        // Validate request
        $validator = $this->validateCheckoutRequest($request);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create or get customer
            $customer = $this->createOrGetCustomer($request);

            // Create order
            $order = $this->createOrder($request, $customer);

            // Process payment based on method
            $paymentResult = $this->processPayment($order, $request);

            if ($paymentResult['success']) {
                // If subscription, create subscription record
                if ($request->product_type === 'subscription' && $order->isPaid()) {
                    $this->createSubscription($order, $request);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'redirect_url' => route('checkout.success', ['order' => $order->order_number]),
                    'payment_data' => $paymentResult,
                ]);
            }

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $paymentResult['message'] ?? 'Payment failed',
                'error' => $paymentResult,
            ], 400);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Checkout process error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your payment. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Success page
     */
    public function success(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['customer', 'subscription'])
            ->firstOrFail();

        return view('checkout.success', [
            'order' => $order,
        ]);
    }

    /**
     * Error page
     */
    public function error(Request $request)
    {
        return view('checkout.error');
    }

    /**
     * Validate checkout request
     */
    private function validateCheckoutRequest(Request $request)
    {
        $rules = [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_cpf' => 'nullable|string|size:11',
            'payment_method' => 'required|in:pix,credit_card',
            'gateway' => 'required|string',
            'product_name' => 'required|string',
            'product_type' => 'required|in:subscription,one_time',
            'amount' => 'required|numeric|min:0.01',
            'terms_accepted' => 'required|accepted',
        ];

        // Additional validation for credit card
        if ($request->payment_method === 'credit_card') {
            $rules['card_number'] = 'required|string|min:13|max:19';
            $rules['card_holder_name'] = 'required|string|max:255';
            $rules['card_exp_month'] = 'required|numeric|min:1|max:12';
            $rules['card_exp_year'] = 'required|numeric|min:' . date('Y');
            $rules['card_cvv'] = 'required|string|min:3|max:4';
        }

        return Validator::make($request->all(), $rules);
    }

    /**
     * Create or get existing customer
     */
    private function createOrGetCustomer(Request $request): Customer
    {
        // Try to find by email first
        $customer = Customer::where('email', $request->customer_email)->first();

        if (!$customer) {
            $customer = Customer::create([
                'name' => $request->customer_name,
                'email' => $request->customer_email,
                'cpf' => $request->customer_cpf,
                'phone' => $request->customer_phone,
            ]);
        }

        return $customer;
    }

    /**
     * Create order
     */
    private function createOrder(Request $request, Customer $customer): Order
    {
        return Order::create([
            'customer_id' => $customer->id,
            'product_name' => $request->product_name,
            'product_type' => $request->product_type,
            'product_description' => $request->product_description,
            'amount' => $request->amount,
            'discount_amount' => $request->discount_amount ?? 0,
            'total_amount' => $request->amount - ($request->discount_amount ?? 0),
            'currency' => 'BRL',
            'payment_method' => $request->payment_method,
            'gateway' => $request->gateway,
            'terms_accepted' => true,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'plan_interval' => $request->plan_interval,
            ],
        ]);
    }

    /**
     * Process payment
     */
    private function processPayment(Order $order, Request $request): array
    {
        $gateway = PaymentGatewayFactory::create($request->gateway);

        if ($request->payment_method === 'pix') {
            $result = $gateway->createPixPayment($order);
            
            if ($result['success']) {
                $order->update([
                    'gateway_transaction_id' => $result['transaction_id'],
                    'pix_qr_code' => $result['qr_code'],
                    'pix_qr_code_url' => $result['qr_code_url'],
                    'pix_expiration' => $result['expiration'],
                    'payment_status' => 'pending',
                ]);
            }
            
            return $result;
        }

        if ($request->payment_method === 'credit_card') {
            $cardData = [
                'number' => $request->card_number,
                'holder_name' => $request->card_holder_name,
                'exp_month' => $request->card_exp_month,
                'exp_year' => $request->card_exp_year,
                'cvv' => $request->card_cvv,
            ];

            $result = $gateway->createCreditCardPayment($order, $cardData);
            
            if ($result['success']) {
                $order->update([
                    'gateway_transaction_id' => $result['transaction_id'],
                    'gateway_charge_id' => $result['charge_id'] ?? null,
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                ]);

                // Save payment method for future use
                if (isset($result['card_brand']) && isset($result['card_last4'])) {
                    PaymentMethod::create([
                        'customer_id' => $order->customer_id,
                        'type' => 'credit_card',
                        'gateway' => $request->gateway,
                        'card_brand' => $result['card_brand'],
                        'card_last4' => $result['card_last4'],
                        'card_exp_month' => $request->card_exp_month,
                        'card_exp_year' => $request->card_exp_year,
                        'cardholder_name' => $request->card_holder_name,
                        'is_default' => true,
                    ]);
                }
            }
            
            return $result;
        }

        return [
            'success' => false,
            'message' => 'Invalid payment method',
        ];
    }

    /**
     * Create subscription
     */
    private function createSubscription(Order $order, Request $request): Subscription
    {
        $interval = $request->plan_interval ?? 'monthly';
        $periodEnd = $interval === 'yearly' 
            ? now()->addYear() 
            : now()->addMonth();

        return Subscription::create([
            'customer_id' => $order->customer_id,
            'order_id' => $order->id,
            'plan_name' => $order->product_name,
            'plan_interval' => $interval,
            'plan_amount' => $order->total_amount,
            'currency' => $order->currency,
            'status' => 'active',
            'gateway' => $order->gateway,
            'started_at' => now(),
            'current_period_start' => now(),
            'current_period_end' => $periodEnd,
        ]);
    }

    /**
     * Get available products (hardcoded for now)
     */
    private function getAvailableProducts(): array
    {
        return [
            'premium' => [
                'id' => 'premium',
                'name' => 'Premium Plan',
                'description' => 'Access to all premium features',
                'price' => 374.00,
                'interval' => 'monthly',
                'type' => 'subscription',
            ],
            'basic' => [
                'id' => 'basic',
                'name' => 'Basic Plan',
                'description' => 'Access to basic features',
                'price' => 199.00,
                'interval' => 'monthly',
                'type' => 'subscription',
            ],
        ];
    }
}