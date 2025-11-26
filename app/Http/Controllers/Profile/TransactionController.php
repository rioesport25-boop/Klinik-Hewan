<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    /**
     * Display user's transaction list
     */
    public function index(Request $request)
    {
        $query = auth()->user()->orders()->with(['items.product.images']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date filter
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        // Transform orders for frontend
        $orders->getCollection()->transform(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_label' => $this->getStatusLabel($order->status),
                'total' => $order->total,
                'items_count' => $order->items->count(),
                'created_at' => $order->created_at->timezone('Asia/Jakarta')->format('d F Y - H:i') . ' WIB',
                'shipping_service' => $order->shipping_service ?? 'Belanja Xpress',
                'first_product' => [
                    'name' => $order->items->first()?->product_name,
                    'image' => $order->items->first()?->product->images->first()?->image_path
                        ? asset('storage/' . $order->items->first()->product->images->first()->image_path)
                        : null,
                ],
            ];
        });

        return Inertia::render('Profile/Transactions/Index', [
            'orders' => $orders,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'date' => $request->date,
            ],
        ]);
    }

    /**
     * Get detailed transaction info
     */
    public function show(Order $order)
    {
        // Check ownership
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['items.product.images', 'payment']);

        return response()->json([
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_label' => $this->getStatusLabel($order->status),
                'total' => $order->total,
                'subtotal' => $order->subtotal,
                'shipping_cost' => $order->shipping_cost,
                'created_at' => $order->created_at->timezone('Asia/Jakarta')->format('d F Y - H:i') . ' WIB',
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'customer_phone' => $order->customer_phone,
                'shipping_address' => $order->shipping_address,
                'shipping_city' => $order->shipping_city,
                'shipping_district' => $order->shipping_district,
                'shipping_province' => $order->shipping_province,
                'shipping_postal_code' => $order->shipping_postal_code,
                'shipping_service' => $order->shipping_service ?? 'Belanja Xpress',
                'notes' => $order->notes,
                'items' => $order->items->map(fn($item) => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_slug' => $item->product?->slug,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->subtotal,
                    'image' => $item->product?->images->first()?->image_path
                        ? asset('storage/' . $item->product->images->first()->image_path)
                        : null,
                ]),
                'payment' => $order->payment ? [
                    'status' => $order->payment->status,
                    'payment_type' => $order->payment->payment_type,
                    'payment_method' => $order->payment->bank
                        ? strtoupper($order->payment->bank) . ' Virtual Account'
                        : ($order->payment->payment_type ?? 'Virtual Account'),
                    'va_number' => $order->payment->va_number,
                    'bank' => $order->payment->bank,
                ] : null,
            ],
        ]);
    }

    /**
     * Get status label in Indonesian
     */
    private function getStatusLabel($status)
    {
        return match ($status) {
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Dibayar',
            'processing' => 'Diproses',
            'shipped' => 'Dikirim',
            'delivered' => 'Selesai',
            'cancelled' => 'Pesanan Dibatalkan',
            default => $status,
        };
    }
}
