<?php

namespace App\Http\Controllers\Petshop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    /**
     * Display the cart page.
     */
    public function show(Request $request)
    {
        $cart = $this->cartService->getCart(createIfMissing: true);

        return Inertia::render('Petshop/Cart/Index', [
            'cart' => $this->cartService->transformCart($cart, summary: false),
        ]);
    }

    /**
     * Add a product to the cart.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::query()
            ->where('is_active', true)
            ->findOrFail($validated['product_id']);

        $variant = null;
        if (! empty($validated['variant_id'])) {
            $variant = ProductVariant::query()
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->findOrFail($validated['variant_id']);
        }

        $cart = $this->cartService->getCart(createIfMissing: true);

        $existingItem = $cart->items()
            ->where('product_id', $product->id)
            ->where('variant_id', $variant?->id)
            ->first();

        $requestedQuantity = (int) $validated['quantity'] + ($existingItem?->quantity ?? 0);

        if (! $this->cartService->validateStock($product, $variant, $requestedQuantity)) {
            throw ValidationException::withMessages([
                'quantity' => 'Stok produk tidak mencukupi.',
            ]);
        }

        $cart->addItem(
            $product->id,
            $variant?->id,
            (int) $validated['quantity']
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Produk berhasil ditambahkan ke keranjang.',
                'cart' => $this->cartService->transformCart(
                    $this->cartService->getCart(createIfMissing: false),
                    summary: false
                ),
            ]);
        }

        return back()->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }

    /**
     * Update the quantity of a cart item.
     */
    public function update(Request $request, int $cartItemId)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $this->cartService->getCart(createIfMissing: false);

        abort_if(! $cart, 404);

        $item = $cart->items()->findOrFail($cartItemId);
        $product = $item->product;
        $variant = $item->variant;

        if (! $this->cartService->validateStock($product, $variant, (int) $validated['quantity'])) {
            throw ValidationException::withMessages([
                'quantity' => 'Stok produk tidak mencukupi.',
            ]);
        }

        $cart->updateItemQuantity($item->id, (int) $validated['quantity']);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Jumlah produk berhasil diperbarui.',
                'cart' => $this->cartService->transformCart(
                    $this->cartService->getCart(createIfMissing: false),
                    summary: false
                ),
            ]);
        }

        return redirect()
            ->route('petshop.cart.show')
            ->with('success', 'Jumlah produk berhasil diperbarui.');
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(Request $request, int $cartItemId)
    {
        $cart = $this->cartService->getCart(createIfMissing: false);

        abort_if(! $cart, 404);

        $cart->removeItem($cartItemId);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Produk berhasil dihapus dari keranjang.',
                'cart' => $this->cartService->transformCart(
                    $this->cartService->getCart(createIfMissing: false),
                    summary: false
                ),
            ]);
        }

        return redirect()
            ->route('petshop.cart.show')
            ->with('success', 'Produk berhasil dihapus dari keranjang.');
    }

    /**
     * Empty the current cart.
     */
    public function clear(Request $request)
    {
        $cart = $this->cartService->getCart(createIfMissing: false);

        if ($cart) {
            $cart->clear();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Keranjang berhasil dikosongkan.',
                'cart' => $this->cartService->transformCart(
                    $this->cartService->getCart(createIfMissing: false),
                    summary: false
                ),
            ]);
        }

        return redirect()
            ->route('petshop.cart.show')
            ->with('success', 'Keranjang berhasil dikosongkan.');
    }
}
