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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            // Plan de suscripción
            $table->string('plan_name');
            $table->string('plan_interval')->default('monthly'); // monthly | yearly
            $table->decimal('plan_amount', 10, 2);
            $table->string('currency', 3)->default('BRL');
            
            // Estado de suscripción
            $table->enum('status', [
                'active',
                'cancelled',
                'suspended',
                'expired',
                'pending'
            ])->default('pending');
            
            // Gateway
            $table->string('gateway');
            $table->string('gateway_subscription_id')->nullable();
            
            // Fechas importantes
            $table->timestamp('started_at')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            // Renovación automática
            $table->boolean('auto_renew')->default(true);
            $table->integer('billing_cycle_anchor')->nullable(); // día del mes para cobro
            
            // Intentos de cobro
            $table->integer('failed_payment_attempts')->default(0);
            $table->timestamp('last_payment_attempt')->nullable();
            $table->timestamp('next_payment_attempt')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('customer_id');
            $table->index('status');
            $table->index('gateway_subscription_id');
            $table->index('current_period_end');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};