<?php

namespace App\Http\Controllers\Petshop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly MidtransService $midtransService
    ) {}

    /**
     * Display the checkout form.
     */
    public function index()
    {
        $cart = $this->cartService->getCart(createIfMissing: false);

        if (! $cart || $cart->items()->count() === 0) {
            return redirect()
                ->route('petshop.index')
                ->with('info', 'Keranjang belanja masih kosong. Silakan pilih produk terlebih dahulu.');
        }

        $cartData = $this->cartService->transformCart($cart, summary: false);

        $user = auth()->user();

        // Get default address if available
        $defaultAddress = $user?->addresses()->where('is_default', true)->first();

        return Inertia::render('Petshop/Checkout/Index', [
            'cart' => $cartData,
            'customer' => [
                'name' => $user?->name ?? '',
                'email' => $user?->email ?? '',
                'phone' => $user?->getAttribute('phone') ?? '',
            ],
            'defaultAddress' => $defaultAddress ? [
                'recipient_name' => $defaultAddress->recipient_name,
                'phone_number' => $defaultAddress->phone_number,
                'full_address' => $defaultAddress->full_address,
                'city' => $defaultAddress->city ?? '',
                'province' => $defaultAddress->province ?? '',
                'postal_code' => $defaultAddress->postal_code ?? '',
            ] : null,
            'shippingMethods' => [
                ['code' => 'regular', 'label' => 'Regular (2-4 hari)', 'fee' => 20000],
                ['code' => 'express', 'label' => 'Express (1-2 hari)', 'fee' => 35000],
                ['code' => 'sameday', 'label' => 'Same Day', 'fee' => 50000],
            ],
        ]);
    }

    /**
     * Process checkout and create Midtrans transaction
     */
    public function store(Request $request)
    {
        $cart = $this->cartService->getCart(createIfMissing: false);

        if (! $cart || $cart->items()->count() === 0) {
            return redirect()
                ->route('petshop.index')
                ->with('info', 'Keranjang belanja masih kosong.');
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'shipping_address' => ['required', 'string', 'max:1000'],
            'shipping_city' => ['required', 'string', 'max:255'],
            'shipping_province' => ['required', 'string', 'max:255'],
            'shipping_postal_code' => ['required', 'string', 'max:20'],
            'shipping_method' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            DB::beginTransaction();

            // Get shipping cost based on method
            $shippingMethods = [
                'regular' => 20000,
                'express' => 35000,
                'sameday' => 50000,
            ];
            $shippingCost = $shippingMethods[$validated['shipping_method']] ?? 0;

            // Calculate totals
            $subtotal = $cart->items->sum(function ($item) {
                return $item->price * $item->quantity;
            });
            $total = $subtotal + $shippingCost;

            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'order_number' => Order::generateOrderNumber(),
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_city' => $validated['shipping_city'],
                'shipping_province' => $validated['shipping_province'],
                'shipping_postal_code' => $validated['shipping_postal_code'],
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'notes' => $validated['notes'],
            ]);

            // Create order items
            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'variant_id' => $cartItem->variant_id,
                    'product_name' => $cartItem->product->name,
                    'variant_name' => $cartItem->variant?->name,
                    'price' => $cartItem->price,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $cartItem->price * $cartItem->quantity,
                ]);

                // Reduce stock
                if ($cartItem->variant_id) {
                    $cartItem->variant->decrement('stock', $cartItem->quantity);
                } else {
                    $cartItem->product->decrement('stock', $cartItem->quantity);
                }
            }

            // Create Midtrans transaction
            $midtransResult = $this->midtransService->createTransaction($order);

            if (!$midtransResult['success']) {
                throw new \Exception($midtransResult['message']);
            }

            DB::commit();

            // Clear cart after successful order (AFTER commit)
            $cart->items()->delete();
            $cart->delete();

            // Redirect to payment page with snap token
            return Inertia::render('Petshop/Payment/Process', [
                'snap_token' => $midtransResult['snap_token'],
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => $order->total,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout Error: ' . $e->getMessage());

            return redirect()
                ->route('petshop.checkout.index')
                ->with('error', 'Terjadi kesalahan saat memproses pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Show payment status page
     */
    public function paymentStatus(Request $request)
    {
        $orderId = $request->get('order_id');
        $order = Order::where('order_number', 'LIKE', "%{$orderId}%")
            ->with(['payment', 'items.product'])
            ->firstOrFail();

        return Inertia::render('Petshop/Payment/Status', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'total' => $order->total,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'payment' => $order->payment ? [
                    'status' => $order->payment->status,
                    'transaction_status' => $order->payment->transaction_status,
                    'payment_type' => $order->payment->getPaymentTypeAttribute(),
                    'va_number' => $order->payment->va_number,
                    'snap_redirect_url' => $order->payment->snap_redirect_url,
                ] : null,
            ],
        ]);
    }
}
