<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'size',
        'color',
        'sku',
        'price_adjustment',
        'stock',
        'is_active',
    ];

    protected $casts = [
        'price_adjustment' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors
    public function getFinalPriceAttribute()
    {
        return $this->product->price + $this->price_adjustment;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }
}
