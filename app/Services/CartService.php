<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CartService
{
    /**
     * Get the current cart for the authenticated user or session.
     */
    public function getCart(bool $createIfMissing = true, bool $withRelations = true): ?Cart
    {
        $cart = null;

        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();

            if (! $cart && $createIfMissing) {
                $cart = Cart::create([
                    'user_id' => Auth::id(),
                    'session_id' => session('cart_session_id'),
                ]);
            }
        } else {
            $sessionId = session()->get('cart_session_id');

            if (! $sessionId) {
                if (! $createIfMissing) {
                    return null;
                }

                $sessionId = (string) Str::uuid();
                session(['cart_session_id' => $sessionId]);
            }

            $cart = Cart::where('session_id', $sessionId)->first();

            if (! $cart && $createIfMissing) {
                $cart = Cart::create([
                    'session_id' => $sessionId,
                ]);
            }
        }

        if ($cart && $withRelations) {
            $cart->load([
                'items.product.images' => function ($query) {
                    $query->orderByDesc('is_primary')->orderBy('order');
                },
                'items.variant',
            ]);
        }

        return $cart;
    }

    /**
     * Convert the cart and its items into a structured array for the frontend.
     */
    public function transformCart(?Cart $cart, bool $summary = false): array
    {
        if (! $cart) {
            return [
                'id' => null,
                'items' => [],
                'total_items' => 0,
                'subtotal' => 0,
            ];
        }

        $cart->loadMissing([
            'items.product.images' => function ($query) {
                $query->orderByDesc('is_primary')->orderBy('order');
            },
            'items.variant',
        ]);

        $items = $cart->items->map(function ($item) {
            $product = $item->product;
            $variant = $item->variant;

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'variant_id' => $item->variant_id,
                'quantity' => $item->quantity,
                'price' => (float) $item->price,
                'subtotal' => (float) $item->subtotal,
                'product' => $product ? [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'stock' => $product->stock,
                    'primary_image_url' => optional($product->primary_image)->image_url,
                ] : null,
                'variant' => $variant ? [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'size' => $variant->size,
                    'color' => $variant->color,
                    'stock' => $variant->stock,
                ] : null,
            ];
        })->values()->all();

        $subtotal = $cart->items->sum(function ($item) {
            return (float) $item->price * (int) $item->quantity;
        });

        $data = [
            'id' => $cart->id,
            'total_items' => (int) $cart->total_items,
            'subtotal' => (float) $subtotal,
        ];

        if (! $summary) {
            $data['items'] = $items;
        }

        return $data;
    }

    /**
     * Ensure the requested quantity does not exceed available stock.
     */
    public function validateStock(Product $product, ?ProductVariant $variant, int $requestedQuantity): bool
    {
        $availableStock = $variant?->stock ?? $product->stock;

        return $availableStock >= $requestedQuantity;
    }
}
