<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    // Accessors
    public function getTotalAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    public function getTotalItemsAttribute()
    {
        return $this->items->sum('quantity');
    }

    // Methods
    public function addItem($productId, $variantId = null, $quantity = 1)
    {
        $existingItem = $this->items()
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->first();

        if ($existingItem) {
            $existingItem->increment('quantity', $quantity);
            return $existingItem;
        }

        $product = Product::findOrFail($productId);
        $price = $product->price;

        if ($variantId) {
            $variant = ProductVariant::findOrFail($variantId);
            $price = $variant->final_price;
        }

        return $this->items()->create([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'quantity' => $quantity,
            'price' => $price,
        ]);
    }

    public function removeItem($cartItemId)
    {
        return $this->items()->where('id', $cartItemId)->delete();
    }

    public function updateItemQuantity($cartItemId, $quantity)
    {
        $item = $this->items()->find($cartItemId);

        if ($item) {
            if ($quantity <= 0) {
                return $this->removeItem($cartItemId);
            }

            $item->update(['quantity' => $quantity]);
            return $item;
        }

        return false;
    }

    public function clear()
    {
        return $this->items()->delete();
    }
}
