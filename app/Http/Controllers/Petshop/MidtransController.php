<?php

namespace App\Http\Controllers\Petshop;

use App\Http\Controllers\Controller;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    public function __construct(private readonly MidtransService $midtransService) {}

    /**
     * Handle notification webhook from Midtrans
     */
    public function notification(Request $request)
    {
        try {
            $notification = $request->all();

            Log::info('Midtrans Notification Received', $notification);

            $result = $this->midtransService->handleNotification($notification);

            if ($result['success']) {
                return response()->json([
                    'status' => 'success',
                    'message' => $result['message'],
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => $result['message'],
            ], 400);
        } catch (\Exception $e) {
            Log::error('Midtrans Notification Handler Error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Handle finish redirect from Midtrans
     */
    public function finish(Request $request)
    {
        $orderId = $request->get('order_id');

        return redirect()
            ->route('petshop.payment.status', ['order_id' => $orderId])
            ->with('success', 'Pembayaran Anda sedang diproses. Silakan tunggu konfirmasi.');
    }

    /**
     * Handle unfinish redirect from Midtrans
     */
    public function unfinish(Request $request)
    {
        $orderId = $request->get('order_id');

        return redirect()
            ->route('petshop.checkout.index')
            ->with('warning', 'Pembayaran belum selesai. Silakan coba lagi.');
    }

    /**
     * Handle error redirect from Midtrans
     */
    public function error(Request $request)
    {
        $orderId = $request->get('order_id');

        return redirect()
            ->route('petshop.checkout.index')
            ->with('error', 'Terjadi kesalahan dalam proses pembayaran. Silakan coba lagi.');
    }
}
