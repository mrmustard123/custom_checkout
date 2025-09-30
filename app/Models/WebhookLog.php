<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'gateway',
        'event_type',
        'event_id',
        'payload',
        'headers',
        'status',
        'order_id',
        'subscription_id',
        'processing_result',
        'processed_at',
        'processing_attempts',
        'last_attempt_at',
    ];

    protected $casts = [
        'headers' => 'array',
        'processed_at' => 'datetime',
        'last_attempt_at' => 'datetime',
    ];

    /**
     * Get the order associated with this webhook
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the subscription associated with this webhook
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get decoded payload
     */
    public function getDecodedPayloadAttribute(): ?array
    {
        return json_decode($this->payload, true);
    }

    /**
     * Mark as processed
     */
    public function markAsProcessed(string $result = null): void
    {
        $this->update([
            'status' => 'processed',
            'processed_at' => now(),
            'processing_result' => $result,
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'processing_result' => $error,
            'processing_attempts' => $this->processing_attempts + 1,
            'last_attempt_at' => now(),
        ]);
    }

    /**
     * Mark as ignored
     */
    public function markAsIgnored(string $reason = null): void
    {
        $this->update([
            'status' => 'ignored',
            'processing_result' => $reason,
        ]);
    }

    /**
     * Check if webhook is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if webhook was processed
     */
    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    /**
     * Check if webhook failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if can retry processing
     */
    public function canRetry(int $maxAttempts = 3): bool
    {
        return $this->processing_attempts < $maxAttempts;
    }

    /**
     * Scope for pending webhooks
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for failed webhooks
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for specific gateway
     */
    public function scopeForGateway($query, string $gateway)
    {
        return $query->where('gateway', $gateway);
    }
}