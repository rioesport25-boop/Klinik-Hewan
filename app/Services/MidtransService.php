<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use Illuminate\Support\Facades\Log;
use Exception;

class MidtransService
{
    public function __construct()
    {
        // Set Midtrans configuration
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Create Snap transaction for an order
     *
     * @param Order $order
     * @return array
     * @throws Exception
     */
    public function createTransaction(Order $order): array
    {
        try {
            // Generate unique order ID for Midtrans
            $orderId = $order->order_number . '-' . time();

            // Prepare item details
            $itemDetails = [];
            foreach ($order->items as $item) {
                $itemDetails[] = [
                    'id' => $item->product_id,
                    'price' => (int) $item->price,
                    'quantity' => $item->quantity,
                    'name' => $item->product_name,
                ];
            }

            // Add shipping cost as item
            if ($order->shipping_cost > 0) {
                $itemDetails[] = [
                    'id' => 'SHIPPING',
                    'price' => (int) $order->shipping_cost,
                    'quantity' => 1,
                    'name' => 'Shipping Cost',
                ];
            }

            // Prepare customer details
            $customerDetails = [
                'first_name' => $order->customer_name,
                'email' => $order->customer_email,
                'phone' => $order->customer_phone,
                'billing_address' => [
                    'address' => $order->shipping_address,
                    'city' => $order->shipping_city,
                    'postal_code' => $order->shipping_postal_code,
                    'country_code' => 'IDN',
                ],
                'shipping_address' => [
                    'address' => $order->shipping_address,
                    'city' => $order->shipping_city,
                    'postal_code' => $order->shipping_postal_code,
                    'country_code' => 'IDN',
                ],
            ];

            // Prepare transaction details
            $transactionDetails = [
                'order_id' => $orderId,
                'gross_amount' => (int) $order->total,
            ];

            // Prepare Snap parameters
            $params = [
                'transaction_details' => $transactionDetails,
                'item_details' => $itemDetails,
                'customer_details' => $customerDetails,
                'enabled_payments' => $this->getEnabledPayments(),
                'callbacks' => [
                    'finish' => config('midtrans.finish_url'),
                    'unfinish' => config('midtrans.unfinish_url'),
                    'error' => config('midtrans.error_url'),
                ],
            ];

            // Get Snap token from Midtrans
            $snapToken = Snap::getSnapToken($params);

            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'midtrans_order_id' => $orderId,
                'amount' => $order->total,
                'total_amount' => $order->total,
                'status' => 'pending',
                'transaction_status' => 'pending',
                'snap_token' => $snapToken,
                'snap_redirect_url' => $this->getSnapUrl($snapToken),
                'expired_at' => now()->addHours(24),
            ]);

            return [
                'success' => true,
                'snap_token' => $snapToken,
                'redirect_url' => $this->getSnapUrl($snapToken),
                'payment' => $payment,
            ];
        } catch (Exception $e) {
            Log::error('Midtrans Create Transaction Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to create payment: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get enabled payment methods
     *
     * @return array
     */
    protected function getEnabledPayments(): array
    {
        return [
            'credit_card',
            'bca_va',
            'bni_va',
            'bri_va',
            'permata_va',
            'other_va',
            'gopay',
            'shopeepay',
            'qris',
            'alfamart',
            'indomaret',
        ];
    }

    /**
     * Get Snap payment URL
     *
     * @param string $snapToken
     * @return string
     */
    protected function getSnapUrl(string $snapToken): string
    {
        $baseUrl = config('midtrans.is_production')
            ? 'https://app.midtrans.com/snap/v2/vtweb/'
            : 'https://app.sandbox.midtrans.com/snap/v2/vtweb/';

        return $baseUrl . $snapToken;
    }

    /**
     * Handle notification from Midtrans
     *
     * @param array $notification
     * @return array
     */
    public function handleNotification(array $notification): array
    {
        try {
            $transactionStatus = $notification['transaction_status'] ?? '';
            $fraudStatus = $notification['fraud_status'] ?? '';
            $orderId = $notification['order_id'] ?? '';

            // Find payment by midtrans_order_id
            $payment = Payment::where('midtrans_order_id', $orderId)->first();

            if (!$payment) {
                Log::warning('Payment not found for order: ' . $orderId);
                return [
                    'success' => false,
                    'message' => 'Payment not found',
                ];
            }

            // Update payment with notification data
            $payment->update([
                'midtrans_transaction_id' => $notification['transaction_id'] ?? null,
                'midtrans_payment_type' => $notification['payment_type'] ?? null,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
                'bank' => $notification['bank'] ?? null,
                'va_number' => $notification['va_numbers'][0]['va_number'] ?? null,
                'bill_key' => $notification['bill_key'] ?? null,
                'biller_code' => $notification['biller_code'] ?? null,
                'pdf_url' => $notification['pdf_url'] ?? null,
                'midtrans_response' => $notification,
            ]);

            // Handle different transaction statuses
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'accept') {
                    $payment->markAsPaid();
                }
            } elseif ($transactionStatus == 'settlement') {
                $payment->markAsPaid();
            } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                $payment->markAsFailed();
            } elseif ($transactionStatus == 'pending') {
                $payment->update(['status' => 'pending']);
            }

            return [
                'success' => true,
                'message' => 'Notification processed successfully',
            ];
        } catch (Exception $e) {
            Log::error('Midtrans Notification Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to process notification: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check transaction status from Midtrans
     *
     * @param string $orderId
     * @return array
     */
    public function checkTransactionStatus(string $orderId): array
    {
        try {
            $status = Transaction::status($orderId);

            return [
                'success' => true,
                'data' => $status,
            ];
        } catch (Exception $e) {
            Log::error('Midtrans Check Status Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to check transaction status: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Cancel transaction
     *
     * @param string $orderId
     * @return array
     */
    public function cancelTransaction(string $orderId): array
    {
        try {
            $result = Transaction::cancel($orderId);

            return [
                'success' => true,
                'data' => $result,
            ];
        } catch (Exception $e) {
            Log::error('Midtrans Cancel Transaction Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to cancel transaction: ' . $e->getMessage(),
            ];
        }
    }
}
