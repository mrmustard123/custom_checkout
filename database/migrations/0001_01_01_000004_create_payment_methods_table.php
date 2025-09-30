<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            
            // Relación con cliente
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            
            // Tipo de método de pago
            $table->string('type'); // credit_card | pix | boleto
            
            // Gateway
            $table->string('gateway');
            $table->string('gateway_payment_method_id')->nullable();
            
            // Información de tarjeta (solo últimos 4 dígitos y metadata)
            $table->string('card_brand')->nullable(); // visa | mastercard | etc
            $table->string('card_last4')->nullable();
            $table->string('card_exp_month', 2)->nullable();
            $table->string('card_exp_year', 4)->nullable();
            $table->string('cardholder_name')->nullable();
            
            // Default payment method
            $table->boolean('is_default')->default(false);
            
            // Estado
            $table->boolean('is_active')->default(true);
            
            // Metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('customer_id');
            $table->index('gateway_payment_method_id');
            $table->index(['customer_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};