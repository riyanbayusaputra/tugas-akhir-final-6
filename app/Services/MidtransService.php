<?php
namespace App\Services;

use App\Models\Order;
use Midtrans\Snap;
use Midtrans\Config;
use Midtrans\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = false;
    }

    public function createTransaction($order, $items)
    {
        $resp = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode(config('services.midtrans.server_key') . ':')
        ]);
       
        // Ensure total_amount is a valid number
        if (!is_numeric($order->total_amount)) {
            return response()->json([
                'message' => 'Invalid total amount'
            ], 400);
        }

        // Build item details array
        $itemDetails = [];

        // Add product items if not empty
        if (!$items->isEmpty()) {
            foreach ($items as $item) {
                $itemDetails[] = [
                    'id' => $item->product_id,
                    'price' => (int) $item->price,
                    'quantity' => $item->quantity,
                    'name' => substr($item->product_name, 0, 50)
                ];
            }
        }

        // Add shipping cost
        if ($order->shipping_cost > 0) {
            $itemDetails[] = [
                'id' => 'SHIPPING',
                'price' => (int) $order->shipping_cost,
                'quantity' => 1,
                'name' => 'Ongkir'
            ];
        }

        // Handle price adjustment (bisa positif atau negatif)
        if ($order->price_adjustment && $order->price_adjustment != 0) {
            if ($order->price_adjustment > 0) {
                // Biaya tambahan
                $itemDetails[] = [
                    'id' => 'PRICE_ADJUSTMENT_ADD',
                    'price' => (int) $order->price_adjustment,
                    'quantity' => 1,
                    'name' => $order->is_custom_catering ? 'Biaya tambahan custom' : 'Biaya tambahan'
                ];
            } else {
                // Pengurangan item (negatif)
                $itemDetails[] = [
                    'id' => 'PRICE_ADJUSTMENT_REDUCE',
                    'price' => (int) $order->price_adjustment, // Tetap negatif
                    'quantity' => 1,
                    'name' => $order->is_custom_catering ? 'Pengurangan item custom' : 'Pengurangan item'
                ];
            }
        }

        $params = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->total_amount,
            ],
            'customer_details' => [
                'first_name' => $order->recipient_name,
                'email' => auth()->user()->email,
                'phone' => $order->phone,
                'shipping_address' => [
                    'first_name' => $order->recipient_name,
                    'phone' => $order->phone,
                    'address' => $order->address_detail,
                    'city' => $order->city,
                    'postal_code' => '',
                    'country_code' => 'IDN',
                ]
            ],
            'item_details' => $itemDetails
        ];

        $response = $resp->post(config('services.midtrans.snap_url'), $params);

        if ($response->status() == 200 || $response->status() == 201) {
            return $response->json()['redirect_url'];
        } else {
            \Log::error('Midtrans API Error', [
                'response_body' => $response->body(),
                'status_code' => $response->status(),
                'order_id' => $order->order_number,
                'params' => $params
            ]);
            
            return response()->json([
                'message' => 'Payment gateway error: ' . $response->body()
            ], 500);
        }
    }

    public function getStatus($order)
    {
        try {
            $status = Transaction::status($order->order_number);
            return [
                'success' => true,
                'message' => 'Success get transaction status',
                'data' => $status
            ];
        } catch(\Exception $e) {
            \Log::error('Midtrans Transaction Status Error', [
                'order_id' => $order->order_number,
                'message' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }
    public function cancel($order)
    {
        try {
            // Pastikan order ada dan memiliki order_number
            if (!$order || !$order->order_number) {
                return [
                    'success' => false,
                    'message' => 'Order atau order number tidak valid',
                    'data' => null
                ];
            }

            // Cek status transaksi terlebih dahulu
            $statusResult = $this->getStatus($order);
            
            if (!$statusResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Tidak dapat mengecek status transaksi: ' . $statusResult['message'],
                    'data' => null
                ];
            }

            $currentStatus = $statusResult['data']->transaction_status ?? null;

            // Cek apakah transaksi bisa dibatalkan
            $cancellableStatuses = ['pending', 'authorize'];
            
            if (!in_array($currentStatus, $cancellableStatuses)) {
                return [
                    'success' => false,
                    'message' => "Transaksi dengan status '{$currentStatus}' tidak dapat dibatalkan",
                    'data' => null
                ];
            }

            // Lakukan pembatalan ke Midtrans
            $response = Transaction::cancel($order->order_number);
            
            \Log::info('Midtrans Transaction Cancelled', [
                'order_id' => $order->order_number,
                'previous_status' => $currentStatus,
                'response' => $response
            ]);

            return [
                'success' => true,
                'message' => 'Transaksi berhasil dibatalkan',
                'data' => $response
            ];
            
        } catch(\Exception $e) {
            \Log::error('Midtrans Transaction Cancel Error', [
                'order_id' => $order->order_number ?? 'unknown',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Gagal membatalkan transaksi: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function expire($order)
    {
        try {
            // Method untuk expire transaksi (alternatif untuk cancel)
            if (!$order || !$order->order_number) {
                return [
                    'success' => false,
                    'message' => 'Order atau order number tidak valid',
                    'data' => null
                ];
            }

            $response = Transaction::expire($order->order_number);
            
            \Log::info('Midtrans Transaction Expired', [
                'order_id' => $order->order_number,
                'response' => $response
            ]);

            return [
                'success' => true,
                'message' => 'Transaksi berhasil di-expire',
                'data' => $response
            ];
            
        } catch(\Exception $e) {
            \Log::error('Midtrans Transaction Expire Error', [
                'order_id' => $order->order_number ?? 'unknown',
                'message' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Gagal melakukan expire transaksi: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
}