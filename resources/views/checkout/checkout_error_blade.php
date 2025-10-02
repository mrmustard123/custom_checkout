@extends('layouts.app')

@section('title', 'Payment Error')

@section('content')
<div class="max-w-2xl mx-auto px-4">
    <div class="bg-white rounded-xl shadow-sm p-8 text-center">
        <!-- Error Icon -->
        <div class="mb-6">
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto">
                <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
        </div>

        <!-- Error Message -->
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            Payment Failed
        </h1>
        <p class="text-gray-600 mb-8">
            We couldn't process your payment. Please try again or contact support if the problem persists.
        </p>

        <!-- Common Issues -->
        <div class="bg-gray-50 rounded-lg p-6 mb-8 text-left">
            <h2 class="font-semibold text-gray-900 mb-4">Common Issues</h2>
            
            <ul class="space-y-3 text-sm text-gray-600">
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Insufficient funds in your account</span>
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Incorrect card information</span>
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Card expired or blocked</span>
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Network or connection issues</span>
                </li>
            </ul>
        </div>

        <!-- Support Contact -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                </svg>
                <div class="text-left">
                    <p class="text-sm font-semibold text-blue-900 mb-1">Need help?</p>
                    <p class="text-sm text-blue-800">
                        Contact our support team at <a href="mailto:support@example.com" class="underline">support@example.com</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/checkout" class="btn-primary">
                Try Again
            </a>
            <a href="/" class="btn-secondary">
                Go to Home
            </a>
        </div>
    </div>
</div>
@endsection
