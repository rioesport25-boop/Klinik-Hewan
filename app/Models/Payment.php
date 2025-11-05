<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'tripay_reference',
        'tripay_merchant_ref',
        'payment_method',
        'payment_channel',
        'payment_name',
        'amount',
        'fee',
        'total_amount',
        'status',
        'payment_code',
        'qr_url',
        'checkout_url',
        'expired_at',
        'paid_at',
        'tripay_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'expired_at' => 'datetime',
        'paid_at' => 'datetime',
        'tripay_response' => 'array',
    ];

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    // Status Check Methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function isExpired()
    {
        return $this->status === 'expired' || ($this->expired_at && $this->expired_at->isPast());
    }

    // Action Methods
    public function markAsPaid()
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Update order status
        $this->order->markAsPaid();
    }

    public function markAsFailed()
    {
        $this->update([
            'status' => 'failed',
        ]);
    }

    public function markAsExpired()
    {
        $this->update([
            'status' => 'expired',
        ]);
    }

    // Tripay Methods
    public function getTripayInstructions()
    {
        if ($this->tripay_response && isset($this->tripay_response['instructions'])) {
            return $this->tripay_response['instructions'];
        }

        return [];
    }

    public function isQris()
    {
        return strtoupper($this->payment_method) === 'QRIS' || strtoupper($this->payment_channel) === 'QRIS';
    }

    public function isVirtualAccount()
    {
        return in_array(strtoupper($this->payment_method), ['VA', 'VIRTUAL_ACCOUNT']) ||
               str_contains(strtoupper($this->payment_channel ?? ''), 'VA');
    }

    public function isEwallet()
    {
        return in_array(strtoupper($this->payment_method), ['EWALLET', 'E-WALLET']) ||
               in_array(strtoupper($this->payment_channel ?? ''), ['OVO', 'DANA', 'SHOPEEPAY', 'GOPAY', 'LINKAJA']);
    }

    // Accessor
    public function getPaymentTypeAttribute()
    {
        if ($this->isQris()) {
            return 'QRIS';
        } elseif ($this->isVirtualAccount()) {
            return 'Virtual Account';
        } elseif ($this->isEwallet()) {
            return 'E-Wallet';
        }

        return $this->payment_method;
    }
}
