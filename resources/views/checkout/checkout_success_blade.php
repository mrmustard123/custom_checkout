@extends('layouts.app')

@section('title', 'Payment Successful!')

@section('content')
<div class="max-w-2xl mx-auto px-4">
    <div class="bg-white rounded-xl shadow-sm p-8 text-center">
        <!-- Success Icon -->
        <div class="mb-6">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </div>

        <!-- Success Message -->
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            Payment Successful!
        </h1>
        <p class="text-gray-600 mb-8">
            Thank you for your purchase. Your payment has been processed successfully.
        </p>

        <!-- Order Details -->
        <div class="bg-gray-50 rounded-lg p-6 mb-8 text-left">
            <h2 class="font-semibold text-gray-900 mb-4">Order Details</h2>
            
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Order Number:</span>
                    <span class="font-semibold">{{ $order->order_number }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-600">Product:</span>
                    <span class="font-semibold">{{ $order->product_name }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-600">Amount Paid:</span>
                    <span class="font-semibold text-green-600">R$ {{ number_format($order->total_amount, 2, ',', '.') }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-600">Payment Method:</span>
                    <span class="font-semibold">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-600">Date:</span>
                    <span class="font-semibold">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        @if($order->subscription)
        <!-- Subscription Info -->
        <div class="bg-blue-50 rounded-lg p-6 mb-8 text-left">
            <h2 class="font-semibold text-gray-900 mb-4">Subscription Active</h2>
            
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Plan:</span>
                    <span class="font-semibold">{{ $order->subscription->plan_name }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-600">Billing Cycle:</span>
                    <span class="font-semibold">{{ ucfirst($order->subscription->plan_interval) }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-600">Next Billing Date:</span>
                    <span class="font-semibold">{{ $order->subscription->current_period_end->format('d/m/Y') }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-600">Status:</span>
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                        {{ ucfirst($order->subscription->status) }}
                    </span>
                </div>
            </div>
        </div>
        @endif

        <!-- Confirmation Email Notice -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-8">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-yellow-800">
                    A confirmation email has been sent to <strong>{{ $order->customer->email }}</strong>
                </p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/" class="btn-primary">
                Go to Dashboard
            </a>
            <a href="/checkout" class="btn-secondary">
                Make Another Purchase
            </a>
        </div>
    </div>
</div>
@endsection