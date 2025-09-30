<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'customer_id',
        'product_name',
        'product_type',
        'product_description',
        'amount',
        'currency',
        'discount_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'gateway',
        'gateway_transaction_id',
        'gateway_charge_id',
        'gateway_response',
        'pix_qr_code',
        'pix_qr_code_url',
        'pix_expiration',
        'terms_accepted',
        'ip_address',
        'user_agent',
        'metadata',
        'paid_at',
        'failed_at',
        'refunded_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'terms_accepted' => 'boolean',
        'metadata' => 'array',
        'pix_expiration' => 'datetime',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    /**
     * Boot method to generate order number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    /**
     * Generate unique order number
     */
    public static function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . strtoupper(Str::random(8));
        } while (self::where('order_number', $number)->exists());

        return $number;
    }

    /**
     * Get the customer that owns the order
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the subscription for this order (if exists)
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    /**
     * Check if order is paid
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if order is pending
     */
    public function isPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    /**
     * Check if order failed
     */
    public function isFailed(): bool
    {
        return $this->payment_status === 'failed';
    }

    /**
     * Mark order as paid
     */
    public function markAsPaid(): void
    {
        $this->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark order as failed
     */
    public function markAsFailed(): void
    {
        $this->update([
            'payment_status' => 'failed',
            'failed_at' => now(),
        ]);
    }

    /**
     * Check if payment method is PIX
     */
    public function isPix(): bool
    {
        return $this->payment_method === 'pix';
    }

    /**
     * Check if payment method is credit card
     */
    public function isCreditCard(): bool
    {
        return $this->payment_method === 'credit_card';
    }

    /**
     * Check if PIX is expired
     */
    public function isPixExpired(): bool
    {
        if (!$this->isPix() || !$this->pix_expiration) {
            return false;
        }

        return now()->isAfter($this->pix_expiration);
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->total_amount, 2, ',', '.');
    }

    /**
     * Scope for paid orders
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope for pending orders
     */
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }
}