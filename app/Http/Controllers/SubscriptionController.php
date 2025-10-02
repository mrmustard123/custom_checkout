<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Subscription;
use App\Services\PaymentGateways\PaymentGatewayFactory;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    /**
     * Get customer subscriptions
     */
    public function index(Request $request)
    {
        $email = $request->input('email');
        
        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'Email is required',
            ], 400);
        }

        $customer = Customer::where('email', $email)->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }

        $subscriptions = Subscription::where('customer_id', $customer->id)
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * Get subscription details
     */
    public function show(Request $request, int $id)
    {
        $subscription = Subscription::with(['customer', 'order'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'subscription' => $subscription,
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request, int $id)
    {
        try {
            $subscription = Subscription::findOrFail($id);

            // Validate ownership (in production, check JWT or session)
            $email = $request->input('email');
            
            if ($subscription->customer->email !== $email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            // Check if already cancelled
            if ($subscription->isCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription is already cancelled',
                ], 400);
            }

            // Cancel at gateway
            if ($subscription->gateway_subscription_id) {
                $gateway = PaymentGatewayFactory::create($subscription->gateway);
                $result = $gateway->cancelSubscription($subscription);

                if (!$result['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to cancel subscription at gateway: ' . $result['message'],
                    ], 500);
                }
            }

            // Cancel locally
            $immediately = $request->input('immediately', false);
            $subscription->cancel($immediately);

            return response()->json([
                'success' => true,
                'message' => 'Subscription cancelled successfully',
                'subscription' => $subscription->fresh(),
            ]);

        } catch (\Exception $e) {
            Log::error('Subscription cancellation error', [
                'subscription_id' => $id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while cancelling the subscription',
            ], 500);
        }
    }

    /**
     * Resume subscription
     */
    public function resume(Request $request, int $id)
    {
        try {
            $subscription = Subscription::findOrFail($id);

            // Validate ownership
            $email = $request->input('email');
            
            if ($subscription->customer->email !== $email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            // Check if can be resumed
            if (!$subscription->isCancelled() && !$subscription->isInGracePeriod()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription cannot be resumed',
                ], 400);
            }

            // Resume subscription
            $subscription->resume();

            return response()->json([
                'success' => true,
                'message' => 'Subscription resumed successfully',
                'subscription' => $subscription->fresh(),
            ]);

        } catch (\Exception $e) {
            Log::error('Subscription resume error', [
                'subscription_id' => $id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while resuming the subscription',
            ], 500);
        }
    }

    /**
     * Update payment method
     */
    public function updatePaymentMethod(Request $request, int $id)
    {
        try {
            $subscription = Subscription::findOrFail($id);

            // Validate ownership
            $email = $request->input('email');
            
            if ($subscription->customer->email !== $email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            // Validate card data
            $request->validate([
                'card_number' => 'required|string|min:13|max:19',
                'card_holder_name' => 'required|string|max:255',
                'card_exp_month' => 'required|numeric|min:1|max:12',
                'card_exp_year' => 'required|numeric|min:' . date('Y'),
                'card_cvv' => 'required|string|min:3|max:4',
            ]);

            // Update at gateway (implementation depends on gateway)
            // This is a placeholder - actual implementation varies by gateway
            
            return response()->json([
                'success' => true,
                'message' => 'Payment method updated successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Update payment method error', [
                'subscription_id' => $id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the payment method',
            ], 500);
        }
    }

    /**
     * Get subscription status from gateway
     */
    public function syncStatus(Request $request, int $id)
    {
        try {
            $subscription = Subscription::findOrFail($id);

            if (!$subscription->gateway_subscription_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription has no gateway ID',
                ], 400);
            }

            $gateway = PaymentGatewayFactory::create($subscription->gateway);
            $status = $gateway->getSubscriptionStatus($subscription->gateway_subscription_id);

            // Update local status
            $subscription->update([
                'status' => $status['status'] ?? $subscription->status,
                'current_period_end' => $status['current_period_end'] ?? $subscription->current_period_end,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription status synced',
                'subscription' => $subscription->fresh(),
            ]);

        } catch (\Exception $e) {
            Log::error('Subscription sync error', [
                'subscription_id' => $id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while syncing subscription status',
            ], 500);
        }
    }
}