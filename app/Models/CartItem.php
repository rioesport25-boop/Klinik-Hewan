<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'variant_id',
        'quantity',
        'price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];

    // Relationships
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    // Accessors
    public function getSubtotalAttribute()
    {
        return $this->price * $this->quantity;
    }

    public function getDisplayNameAttribute()
    {
        $name = $this->product->name;

        if ($this->variant) {
            $variantDetails = [];
            if ($this->variant->size) {
                $variantDetails[] = $this->variant->size;
            }
            if ($this->variant->color) {
                $variantDetails[] = $this->variant->color;
            }

            if (!empty($variantDetails)) {
                $name .= ' (' . implode(', ', $variantDetails) . ')';
            }
        }

        return $name;
    }
}
