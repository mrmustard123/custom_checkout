<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'order_id',
        'plan_name',
        'plan_interval',
        'plan_amount',
        'currency',
        'status',
        'gateway',
        'gateway_subscription_id',
        'started_at',
        'current_period_start',
        'current_period_end',
        'cancelled_at',
        'expires_at',
        'auto_renew',
        'billing_cycle_anchor',
        'failed_payment_attempts',
        'last_payment_attempt',
        'next_payment_attempt',
        'metadata',
    ];

    protected $casts = [
        'plan_amount' => 'decimal:2',
        'auto_renew' => 'boolean',
        'started_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'cancelled_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_payment_attempt' => 'datetime',
        'next_payment_attempt' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the customer that owns the subscription
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the order that created this subscription
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if subscription is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' || 
               ($this->expires_at && now()->isAfter($this->expires_at));
    }

    /**
     * Check if subscription is in grace period (cancelled but still active)
     */
    public function isInGracePeriod(): bool
    {
        return $this->cancelled_at && 
               $this->current_period_end && 
               now()->isBefore($this->current_period_end);
    }

    /**
     * Activate subscription
     */
    public function activate(): void
    {
        $this->update([
            'status' => 'active',
            'started_at' => $this->started_at ?? now(),
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancel(bool $immediately = false): void
    {
        $updates = [
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'auto_renew' => false,
        ];

        if ($immediately) {
            $updates['expires_at'] = now();
        } else {
            // Cancel at end of period
            $updates['expires_at'] = $this->current_period_end;
        }

        $this->update($updates);
    }

    /**
     * Suspend subscription
     */
    public function suspend(): void
    {
        $this->update([
            'status' => 'suspended',
        ]);
    }

    /**
     * Resume subscription
     */
    public function resume(): void
    {
        $this->update([
            'status' => 'active',
            'cancelled_at' => null,
        ]);
    }

    /**
     * Increment failed payment attempts
     */
    public function incrementFailedAttempts(): void
    {
        $this->increment('failed_payment_attempts');
        $this->update([
            'last_payment_attempt' => now(),
        ]);
    }

    /**
     * Reset failed payment attempts
     */
    public function resetFailedAttempts(): void
    {
        $this->update([
            'failed_payment_attempts' => 0,
            'last_payment_attempt' => null,
        ]);
    }

    /**
     * Check if subscription needs renewal
     */
    public function needsRenewal(): bool
    {
        return $this->isActive() && 
               $this->current_period_end && 
               now()->isAfter($this->current_period_end);
    }

    /**
     * Get days until expiration
     */
    public function daysUntilExpiration(): ?int
    {
        if (!$this->current_period_end) {
            return null;
        }

        return now()->diffInDays($this->current_period_end, false);
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->plan_amount, 2, ',', '.');
    }

    /**
     * Scope for active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for cancelled subscriptions
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope for expired subscriptions
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }
}