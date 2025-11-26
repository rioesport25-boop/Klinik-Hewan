<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'payment_method',
        'payment_channel',
        'midtrans_payment_type',
        'payment_name',
        'bank',
        'va_number',
        'bill_key',
        'biller_code',
        'pdf_url',
        'amount',
        'fee',
        'total_amount',
        'status',
        'transaction_status',
        'fraud_status',
        'snap_token',
        'snap_redirect_url',
        'expired_at',
        'paid_at',
        'midtrans_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'expired_at' => 'datetime',
        'paid_at' => 'datetime',
        'midtrans_response' => 'array',
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

        // Create notification for successful payment
        $this->order->user->notifications()->create([
            'type' => 'payment_success',
            'title' => 'Pembayaran Berhasil',
            'message' => "Pembayaran untuk pesanan {$this->order->order_number} telah berhasil. Pesanan Anda sedang diproses.",
            'data' => [
                'order_id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'total' => $this->order->total,
                'items_count' => $this->order->items->count(),
                'product_image' => $this->order->items->first()->product->images->first()?->image_path
                    ? asset('storage/' . $this->order->items->first()->product->images->first()->image_path)
                    : null,
                'product_name' => $this->order->items->first()->product->name ?? '',
            ],
        ]);
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

    // Midtrans Methods
    public function getMidtransInstructions()
    {
        if ($this->midtrans_response && isset($this->midtrans_response['va_numbers'])) {
            return $this->midtrans_response['va_numbers'];
        }

        return [];
    }

    public function isQris()
    {
        return strtoupper($this->midtrans_payment_type ?? '') === 'QRIS' ||
            strtoupper($this->payment_channel ?? '') === 'QRIS';
    }

    public function isVirtualAccount()
    {
        return in_array(strtoupper($this->midtrans_payment_type ?? ''), ['BANK_TRANSFER', 'ECHANNEL']) ||
            str_contains(strtoupper($this->payment_channel ?? ''), 'VA') ||
            !empty($this->va_number);
    }

    public function isEwallet()
    {
        return in_array(strtoupper($this->midtrans_payment_type ?? ''), ['GOPAY', 'SHOPEEPAY']) ||
            in_array(strtoupper($this->payment_channel ?? ''), ['GOPAY', 'SHOPEEPAY']);
    }

    public function isCreditCard()
    {
        return strtoupper($this->midtrans_payment_type ?? '') === 'CREDIT_CARD';
    }

    public function isConvenienceStore()
    {
        return in_array(strtoupper($this->midtrans_payment_type ?? ''), ['CSTORE', 'ALFAMART', 'INDOMARET']);
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
        } elseif ($this->isCreditCard()) {
            return 'Credit Card';
        } elseif ($this->isConvenienceStore()) {
            return 'Convenience Store';
        }

        return $this->midtrans_payment_type ?? $this->payment_method;
    }

    // Transaction Status Checkers
    public function isTransactionPending()
    {
        return in_array($this->transaction_status, ['pending', 'authorize']);
    }

    public function isTransactionSuccess()
    {
        return in_array($this->transaction_status, ['capture', 'settlement']);
    }

    public function isTransactionDenied()
    {
        return $this->transaction_status === 'deny';
    }

    public function isTransactionCancelled()
    {
        return in_array($this->transaction_status, ['cancel', 'expire']);
    }
}
