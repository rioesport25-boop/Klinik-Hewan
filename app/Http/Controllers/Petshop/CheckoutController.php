<?php

namespace App\Http\Controllers\Petshop;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function __construct(private readonly CartService $cartService)
    {
    }

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

        return Inertia::render('Petshop/Checkout/Index', [
            'cart' => $cartData,
            'customer' => [
                'name' => $user?->name ?? '',
                'email' => $user?->email ?? '',
                'phone' => $user?->getAttribute('phone') ?? '',
            ],
            'shippingMethods' => [
                ['code' => 'regular', 'label' => 'Regular (2-4 hari)', 'fee' => 20000],
                ['code' => 'express', 'label' => 'Express (1-2 hari)', 'fee' => 35000],
                ['code' => 'sameday', 'label' => 'Same Day', 'fee' => 50000],
            ],
            'paymentChannels' => [
                ['code' => 'qris', 'label' => 'QRIS (Tripay)', 'description' => 'Scan QR code melalui Tripay'],
                ['code' => 'va_bca', 'label' => 'Virtual Account BCA', 'description' => 'Pembayaran otomatis via VA BCA'],
                ['code' => 'va_bri', 'label' => 'Virtual Account BRI', 'description' => 'Pembayaran otomatis via VA BRI'],
                ['code' => 'ewallet_dana', 'label' => 'DANA', 'description' => 'Pembayaran e-wallet melalui Tripay'],
            ],
        ]);
    }

    /**
     * Temporary handler for checkout submission.
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
            'payment_channel' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Placeholder response until full checkout & Tripay integration is implemented.
        return redirect()
            ->route('petshop.checkout.index')
            ->with('success', 'Checkout berhasil disimpan. Integrasi pembayaran Tripay akan ditambahkan pada fase berikutnya.');
    }
}
