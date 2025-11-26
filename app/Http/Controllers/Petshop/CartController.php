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
    public function __construct(private readonly CartService $cartService) {}

    /**
     * Display the cart page.
     */
    public function show(Request $request)
    {
        $cart = $this->cartService->getCart(createIfMissing: true);

        // Get default shipping address if user is authenticated
        $defaultAddress = null;
        if ($user = auth()->user()) {
            $address = $user->addresses()->where('is_default', true)->first();
            if ($address) {
                $defaultAddress = [
                    'label' => $address->label,
                    'recipient_name' => $address->recipient_name,
                    'phone_number' => $address->phone_number,
                    'full_address' => $address->full_address,
                    'city' => $address->city,
                    'province' => $address->province,
                    'postal_code' => $address->postal_code,
                ];
            }
        }

        return Inertia::render('Petshop/Cart/Index', [
            'cart' => $this->cartService->transformCart($cart, summary: false),
            'defaultAddress' => $defaultAddress,
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
     * Remove multiple items from the cart.
     */
    public function removeMultiple(Request $request)
    {
        $validated = $request->validate([
            'item_ids' => ['required', 'array'],
            'item_ids.*' => ['required', 'integer'],
        ]);

        $cart = $this->cartService->getCart(createIfMissing: false);

        abort_if(! $cart, 404);

        foreach ($validated['item_ids'] as $itemId) {
            $cart->removeItem($itemId);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => count($validated['item_ids']) . ' produk berhasil dihapus dari keranjang.',
                'cart' => $this->cartService->transformCart(
                    $this->cartService->getCart(createIfMissing: false),
                    summary: false
                ),
            ]);
        }

        return redirect()
            ->route('petshop.cart.show')
            ->with('success', count($validated['item_ids']) . ' produk berhasil dihapus dari keranjang.');
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

    /**
     * Process checkout and create Midtrans payment.
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'selected_items' => ['required', 'array', 'min:1'],
            'selected_items.*' => ['required', 'integer', 'exists:cart_items,id'],
            'delivery_type' => ['required', 'in:delivery,pickup'],
            'delivery_option' => ['nullable', 'in:instant,regular'],
            'delivery_date' => ['nullable', 'in:today,tomorrow'],
            'delivery_time' => ['nullable', 'string'],
            'shipping_fee' => ['required', 'numeric', 'min:0'],
            'shipping_address' => ['nullable', 'array'],
            'shipping_address.recipient_name' => ['required_if:delivery_type,delivery', 'string'],
            'shipping_address.phone_number' => ['required_if:delivery_type,delivery', 'string'],
            'shipping_address.full_address' => ['required_if:delivery_type,delivery', 'string'],
            'shipping_address.city' => ['required_if:delivery_type,delivery', 'string'],
            'shipping_address.district' => ['nullable', 'string'],
            'shipping_address.province' => ['required_if:delivery_type,delivery', 'string'],
            'shipping_address.postal_code' => ['required_if:delivery_type,delivery', 'string'],
        ]);

        $cart = $this->cartService->getCart(createIfMissing: false);

        if (! $cart || $cart->items->isEmpty()) {
            return back()->with('error', 'Keranjang Anda kosong.');
        }

        // Get only selected items
        $selectedItems = $cart->items->whereIn('id', $validated['selected_items']);

        if ($selectedItems->isEmpty()) {
            return back()->with('error', 'Silakan pilih minimal satu produk untuk checkout.');
        }

        // Validate shipping address for delivery
        if ($validated['delivery_type'] === 'delivery' && empty($validated['shipping_address'])) {
            return back()->with('error', 'Silakan tambahkan alamat pengiriman terlebih dahulu.');
        }

        // Calculate totals from selected items only
        $subtotal = $selectedItems->sum('subtotal');
        $shippingCost = $validated['shipping_fee'];
        $totalAmount = $subtotal + $shippingCost;

        // Prepare shipping address data
        $shippingAddressData = $validated['shipping_address'] ?? [];
        $shippingAddress = '';
        $shippingCity = '';
        $shippingProvince = '';
        $shippingPostalCode = '';
        $customerPhone = '';

        if ($validated['delivery_type'] === 'delivery' && !empty($shippingAddressData)) {
            $shippingAddress = json_encode($shippingAddressData);
            $shippingCity = $shippingAddressData['city'];
            $shippingProvince = $shippingAddressData['province'];
            $shippingPostalCode = $shippingAddressData['postal_code'];
            $customerPhone = $shippingAddressData['phone_number'];
        }

        // Create order
        $order = auth()->user()->orders()->create([
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'customer_name' => auth()->user()->name,
            'customer_email' => auth()->user()->email,
            'customer_phone' => $customerPhone ?: auth()->user()->email,
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'total' => $totalAmount,
            'status' => 'pending',
            'shipping_address' => $shippingAddress,
            'shipping_city' => $shippingCity,
            'shipping_province' => $shippingProvince,
            'shipping_postal_code' => $shippingPostalCode,
            'delivery_type' => $validated['delivery_type'],
            'delivery_option' => $validated['delivery_option'],
            'delivery_date' => $validated['delivery_date'],
            'delivery_time' => $validated['delivery_time'],
        ]);

        // Create order items from selected items only
        foreach ($selectedItems as $item) {
            $order->items()->create([
                'product_id' => $item->product_id,
                'variant_id' => $item->variant_id,
                'product_name' => $item->product->name,
                'variant_name' => $item->variant?->name ?? null,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'subtotal' => $item->subtotal,
            ]);
        }

        // Create Midtrans payment
        \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = config('services.midtrans.is_production');
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->total,
            ],
            'customer_details' => [
                'first_name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'phone' => $customerPhone ?: '',
            ],
            'item_details' => $selectedItems->map(function ($item) {
                return [
                    'id' => $item->product_id,
                    'price' => (int) $item->price,
                    'quantity' => $item->quantity,
                    'name' => $item->product->name,
                ];
            })->toArray(),
        ];

        // Add shipping fee as item
        if ($validated['shipping_fee'] > 0) {
            $params['item_details'][] = [
                'id' => 'shipping',
                'price' => (int) $validated['shipping_fee'],
                'quantity' => 1,
                'name' => 'Biaya Pengiriman',
            ];
        }

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);

            // Save snap token to order
            $order->update(['snap_token' => $snapToken]);

            // Remove only selected items from cart
            foreach ($selectedItems as $item) {
                $item->delete();
            }

            // Create notification for user
            auth()->user()->notifications()->create([
                'type' => 'order_placed',
                'title' => 'Pesanan Berhasil Dibuat',
                'message' => "Pesanan {$order->order_number} berhasil dibuat. Silakan selesaikan pembayaran Anda.",
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => $order->total,
                    'items_count' => $order->items->count(),
                    'product_image' => $order->items->first()->product->images->first()?->image_path
                        ? asset('storage/' . $order->items->first()->product->images->first()->image_path)
                        : null,
                    'product_name' => $order->items->first()->product->name ?? '',
                ],
            ]);

            return back()->with([
                'snap_token' => $snapToken,
                'order_number' => $order->order_number,
            ]);
        } catch (\Exception $e) {
            // Delete order if payment creation failed
            $order->delete();

            // Log error for debugging
            \Log::error('Midtrans Snap Token Error', [
                'message' => $e->getMessage(),
                'order_number' => $order->order_number,
                'user_id' => auth()->id(),
            ]);

            return back()->withErrors([
                'message' => 'Gagal membuat pembayaran: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display payment status page
     */
    public function paymentStatus(Request $request)
    {
        $orderNumber = $request->get('order_number');

        if (!$orderNumber) {
            return redirect()->route('petshop.products.index')
                ->with('error', 'Nomor pesanan tidak ditemukan.');
        }

        $order = auth()->user()->orders()->where('order_number', $orderNumber)->first();

        if (!$order) {
            return redirect()->route('petshop.products.index')
                ->with('error', 'Pesanan tidak ditemukan.');
        }

        // Check payment status from Midtrans
        try {
            \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
            \Midtrans\Config::$isProduction = config('services.midtrans.is_production');

            $status = \Midtrans\Transaction::status($orderNumber);

            // Update order status based on Midtrans response
            if ($status->transaction_status == 'settlement' || $status->transaction_status == 'capture') {
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'processing',
                    'paid_at' => now(),
                ]);
            } elseif ($status->transaction_status == 'pending') {
                $order->update([
                    'payment_status' => 'pending',
                ]);
            } elseif (in_array($status->transaction_status, ['deny', 'expire', 'cancel'])) {
                $order->update([
                    'payment_status' => 'failed',
                    'status' => 'cancelled',
                ]);
            }
        } catch (\Exception $e) {
            // If error checking status, just show current order status
        }

        return Inertia::render('Petshop/Payment/Status', [
            'order' => [
                'order_number' => $order->order_number,
                'total' => $order->total,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'created_at' => $order->created_at->format('d F Y H:i'),
            ],
        ]);
    }
}
