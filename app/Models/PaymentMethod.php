<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'type',
        'gateway',
        'gateway_payment_method_id',
        'card_brand',
        'card_last4',
        'card_exp_month',
        'card_exp_year',
        'cardholder_name',
        'is_default',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Boot method to ensure only one default payment method per customer
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($paymentMethod) {
            if ($paymentMethod->is_default) {
                // Remove default flag from other payment methods
                self::where('customer_id', $paymentMethod->customer_id)
                    ->where('id', '!=', $paymentMethod->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * Get the customer that owns the payment method
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Check if payment method is a credit card
     */
    public function isCreditCard(): bool
    {
        return $this->type === 'credit_card';
    }

    /**
     * Check if card is expired
     */
    public function isExpired(): bool
    {
        if (!$this->isCreditCard() || !$this->card_exp_month || !$this->card_exp_year) {
            return false;
        }

        $expDate = \Carbon\Carbon::createFromDate($this->card_exp_year, $this->card_exp_month, 1)->endOfMonth();
        return now()->isAfter($expDate);
    }

    /**
     * Get masked card number
     */
    public function getMaskedCardNumberAttribute(): ?string
    {
        if (!$this->isCreditCard() || !$this->card_last4) {
            return null;
        }

        return '**** **** **** ' . $this->card_last4;
    }

    /**
     * Get card expiration as formatted string
     */
    public function getCardExpirationAttribute(): ?string
    {
        if (!$this->card_exp_month || !$this->card_exp_year) {
            return null;
        }

        return sprintf('%02d/%s', $this->card_exp_month, substr($this->card_exp_year, -2));
    }

    /**
     * Get display name for payment method
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->isCreditCard()) {
            return ucfirst($this->card_brand ?? 'Card') . ' •••• ' . $this->card_last4;
        }

        return ucfirst(str_replace('_', ' ', $this->type));
    }

    /**
     * Set as default payment method
     */
    public function setAsDefault(): void
    {
        $this->update(['is_default' => true]);
    }

    /**
     * Deactivate payment method
     */
    public function deactivate(): void
    {
        $this->update([
            'is_active' => false,
            'is_default' => false,
        ]);
    }

    /**
     * Activate payment method
     */
    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Scope for active payment methods
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default payment method
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}