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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            
            // Relación con cliente
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            
            // Información del producto/plan
            $table->string('product_name');
            $table->string('product_type')->default('subscription'); // subscription | one_time
            $table->text('product_description')->nullable();
            
            // Montos
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('BRL');
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            
            // Información de pago
            $table->string('payment_method'); // pix | credit_card
            $table->enum('payment_status', [
                'pending', 
                'processing', 
                'paid', 
                'failed', 
                'refunded',
                'cancelled'
            ])->default('pending');
            
            // Gateway de pago
            $table->string('gateway'); // pagarme | stripe | mercadopago
            $table->string('gateway_transaction_id')->nullable();
            $table->string('gateway_charge_id')->nullable();
            $table->text('gateway_response')->nullable(); // JSON response del gateway
            
            // PIX específico
            $table->text('pix_qr_code')->nullable();
            $table->string('pix_qr_code_url')->nullable();
            $table->dateTime('pix_expiration')->nullable();
            
            // Términos y condiciones
            $table->boolean('terms_accepted')->default(false);
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            
            // Metadata adicional
            $table->json('metadata')->nullable();
            
            // Timestamps de estados
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('order_number');
            $table->index('customer_id');
            $table->index('payment_status');
            $table->index('gateway');
            $table->index('gateway_transaction_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};