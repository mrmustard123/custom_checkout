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
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            
            // Gateway que envió el webhook
            $table->string('gateway');
            
            // Tipo de evento
            $table->string('event_type');
            
            // ID del evento en el gateway
            $table->string('event_id')->nullable();
            
            // Payload completo
            $table->longText('payload');
            
            // Headers de la request
            $table->json('headers')->nullable();
            
            // Estado del procesamiento
            $table->enum('status', [
                'pending',
                'processed',
                'failed',
                'ignored'
            ])->default('pending');
            
            // Relaciones (nullable porque puede no estar relacionado aún)
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
            
            // Información de procesamiento
            $table->text('processing_result')->nullable();
            $table->timestamp('processed_at')->nullable();
            
            // Intentos de reprocesamiento
            $table->integer('processing_attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index('gateway');
            $table->index('event_type');
            $table->index('event_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};