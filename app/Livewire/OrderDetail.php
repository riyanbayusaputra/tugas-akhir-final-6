<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PaymentMethod;
use App\Models\Order;
use App\Models\Store;
use Carbon\Carbon;
use App\Services\OrderStatusService;
use App\Services\MidtransService;
use App\Notifications\OrderCancelledNotification;
use App\Notifications\OrderDeliveryConfirmedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class OrderDetail extends Component
{
    public $order;
    public $paymentDeadline;
    public $paymentMethods;
    public $store;
    
    // Property untuk modal konfirmasi pembatalan
    public $showCancelModal = false;
    public $cancelReason = '';

    public function mount($orderNumber)
    {
        $this->order = Order::where('order_number', $orderNumber)
                        ->where('user_id', auth()->id()) // Tambah security check
                        ->firstOrFail();
                        
        $this->paymentDeadline = Carbon::parse($this->order->created_at)->addHours(12);
        $this->paymentMethods = PaymentMethod::all();
        $this->store = Store::first();
    }

    public function getStatusInfo()
    {
        return OrderStatusService::getStatusInfo(
            $this->order->status,
            $this->paymentDeadline,
            $this->order->completed_at,
            $this->order->payment_gateway_transaction_id
        );
    }

    /**
     * Cek apakah order bisa dibatalkan oleh user
     */
    public function canCancelOrder()
    {
        // User hanya bisa membatalkan jika status masih 'checking' atau 'pending'
        return in_array($this->order->status, [
            OrderStatusService::STATUS_CHECKING,
            OrderStatusService::STATUS_PENDING
        ]) && $this->order->payment_status !== OrderStatusService::PAYMENT_PAID;
    }

    /**
     * Tampilkan modal konfirmasi pembatalan
     */
    public function showCancelConfirmation()
    {
        if (!$this->canCancelOrder()) {
            $this->dispatch('showAlert', [
                'message' => 'Pesanan tidak dapat dibatalkan pada tahap ini.',
                'type' => 'error'
            ]);
            return;
        }

        $this->showCancelModal = true;
        $this->cancelReason = '';
    }

    /**
     * Tutup modal pembatalan
     */
    public function closeCancelModal()
    {
        $this->showCancelModal = false;
        $this->cancelReason = '';
    }

    /**
     * Proses pembatalan pesanan
     */
    public function cancelOrder()
    {
        // Validasi
        if (!$this->canCancelOrder()) {
            $this->dispatch('showAlert', [
                'message' => 'Pesanan tidak dapat dibatalkan pada tahap ini.',
                'type' => 'error'
            ]);
            return;
        }

        // Validasi alasan pembatalan
        if (empty(trim($this->cancelReason))) {
            $this->addError('cancelReason', 'Alasan pembatalan wajib diisi.');
            return;
        }

        if (strlen(trim($this->cancelReason)) < 10) {
            $this->addError('cancelReason', 'Alasan pembatalan minimal 10 karakter.');
            return;
        }

        DB::beginTransaction();
        
        try {
            // Update status order
            $this->order->update([
                'status' => OrderStatusService::STATUS_CANCELLED,
                'payment_status' => OrderStatusService::PAYMENT_UNPAID,
                'cancelled_at' => now(),
                'cancel_reason' => trim($this->cancelReason),
                'cancelled_by' => 'user' // Menandai dibatalkan oleh user
            ]);

            // Jika ada transaksi Midtrans yang pending, coba cancel
            if ($this->order->payment_gateway_transaction_id) {
                try {
                    $midtrans = app(MidtransService::class);
                    $midtrans->cancel($this->order->payment_gateway_transaction_id);
                } catch (\Exception $e) {
                    Log::warning('Failed to cancel Midtrans transaction: ' . $e->getMessage(), [
                        'order_id' => $this->order->id,
                        'transaction_id' => $this->order->payment_gateway_transaction_id
                    ]);
                    // Tidak throw error karena pembatalan order sudah berhasil
                }
            }

            // Kirim notifikasi email ke admin store
            $this->sendCancellationEmailNotification();

            DB::commit();

            // Refresh order data
            $this->order->refresh();
            
            // Tutup modal
            $this->closeCancelModal();

            // Tampilkan notifikasi sukses
            $this->dispatch('showAlert', [
                'message' => 'Pesanan berhasil dibatalkan.',
                'type' => 'success'
            ]);

            Log::info('Order cancelled by user', [
                'order_id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'user_id' => auth()->id(),
                'reason' => $this->cancelReason
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to cancel order: ' . $e->getMessage(), [
                'order_id' => $this->order->id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('showAlert', [
                'message' => 'Terjadi kesalahan saat membatalkan pesanan. Silakan coba lagi.',
                'type' => 'error'
            ]);
        }
    }

    /**
     * Konfirmasi barang sudah sampai
     */
    public function confirmArrival()
    {
        // Validasi apakah order bisa dikonfirmasi
        if ($this->order->status !== OrderStatusService::STATUS_SHIPPED) {
            $this->dispatch('showAlert', [
                'message' => 'Pesanan belum dapat dikonfirmasi kedatangan.',
                'type' => 'error'
            ]);
            return;
        }

        if ($this->order->confirmed_arrival) {
            $this->dispatch('showAlert', [
                'message' => 'Pesanan sudah dikonfirmasi sebelumnya.',
                'type' => 'warning'
            ]);
            return;
        }

        DB::beginTransaction();
        
        try {
            // Update order dengan konfirmasi kedatangan
            $this->order->update([
                'confirmed_arrival' => true,
                'confirmed_arrival_at' => now()
            ]);

            // Kirim notifikasi email ke admin store
            $this->sendDeliveryConfirmationEmailNotification();

            DB::commit();

            // Refresh order data
            $this->order->refresh();

            // Tampilkan notifikasi sukses
            $this->dispatch('showAlert', [
                'message' => 'Terima kasih! Konfirmasi kedatangan berhasil disimpan.',
                'type' => 'success'
            ]);

            Log::info('Order delivery confirmed by user', [
                'order_id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'user_id' => auth()->id(),
                'confirmed_at' => now()
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to confirm order delivery: ' . $e->getMessage(), [
                'order_id' => $this->order->id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('showAlert', [
                'message' => 'Terjadi kesalahan saat mengkonfirmasi kedatangan. Silakan coba lagi.',
                'type' => 'error'
            ]);
        }
    }

    /**
     * Kirim notifikasi email pembatalan ke admin store
     */
    private function sendCancellationEmailNotification()
    {
        try {
            // Pastikan store memiliki email notification
            if ($this->store && $this->store->email_notification) {
                // Kirim menggunakan notification system Laravel
                Notification::route('mail', $this->store->email_notification)
                    ->notify(new OrderCancelledNotification($this->order, $this->cancelReason));
                
                Log::info('Cancellation email notification sent to admin', [
                    'order_id' => $this->order->id,
                    'order_number' => $this->order->order_number,
                    'admin_email' => $this->store->email_notification
                ]);
            } else {
                Log::warning('No admin email configured for cancellation notification', [
                    'order_id' => $this->order->id,
                    'store_id' => $this->store?->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send cancellation email notification: ' . $e->getMessage(), [
                'order_id' => $this->order->id,
                'admin_email' => $this->store->email_notification ?? 'not_set',
                'error' => $e->getMessage()
            ]);
            // Tidak throw exception karena ini hanya notifikasi tambahan
        }
    }

    /**
     * Kirim notifikasi email konfirmasi kedatangan ke admin store
     */
    private function sendDeliveryConfirmationEmailNotification()
    {
        try {
            // Pastikan store memiliki email notification
            if ($this->store && $this->store->email_notification) {
                // Kirim menggunakan notification system Laravel
                Notification::route('mail', $this->store->email_notification)
                    ->notify(new OrderDeliveryConfirmedNotification($this->order));
                
                Log::info('Delivery confirmation email notification sent to admin', [
                    'order_id' => $this->order->id,
                    'order_number' => $this->order->order_number,
                    'admin_email' => $this->store->email_notification
                ]);
            } else {
                Log::warning('No admin email configured for delivery confirmation notification', [
                    'order_id' => $this->order->id,
                    'store_id' => $this->store?->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send delivery confirmation email notification: ' . $e->getMessage(), [
                'order_id' => $this->order->id,
                'admin_email' => $this->store->email_notification ?? 'not_set',
                'error' => $e->getMessage()
            ]);
            // Tidak throw exception karena ini hanya notifikasi tambahan
        }
    }

    public function render()
    {
        $statusInfo = $this->getStatusInfo();
        $this->checkPaymentStatus();
       
        return view('livewire.order-detail', [
            'statusInfo' => $statusInfo,
            'order' => $this->order,
            'canCancel' => $this->canCancelOrder()
        ])->layout('components.layouts.app', ['hideBottomNav' => true]);
    }

    public function checkPaymentStatus()
    {
        if ($this->order && $this->order->payment_gateway_transaction_id && 
            $this->order->status !== OrderStatusService::STATUS_CANCELLED) {
            try {
                $midtrans = app(MidtransService::class);
                $status = $midtrans->getStatus($this->order);
                $latestStatus = $status['data']->transaction_status;

                // Cek apakah status berubah
                if ($latestStatus) {
                    // Update database jika ada perubahan status
                    $this->order->update([
                        'payment_gateway_data' => json_encode($status['data'])
                    ]);

                    switch ($latestStatus) {
                        case 'settlement':
                            $this->order->update([
                                'payment_status' => OrderStatusService::PAYMENT_PAID,
                            ]);
                            break;
                        case 'pending':
                            $this->order->update([
                                'payment_status' => OrderStatusService::PAYMENT_UNPAID,
                                'status' => OrderStatusService::STATUS_PENDING
                            ]);
                            break;
                        case 'deny':
                        case 'cancel':
                        case 'expire':
                            $this->order->update([
                                'payment_status' => OrderStatusService::PAYMENT_UNPAID,
                                'status' => OrderStatusService::STATUS_CANCELLED,
                                'cancelled_at' => now(),
                                'cancel_reason' => 'Pembayaran ' . $latestStatus,
                                'cancelled_by' => 'system'
                            ]);
                            break;
                    }
                    
                    // Refresh order setelah update
                    $this->order->refresh();
                }
            } catch (\Exception $e) {
                Log::error('Error checking payment status: ' . $e->getMessage());
            }
        }
    }
}