<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCancelledNotification extends Notification
{
    public $order;
    public $cancelReason;

    public function __construct(Order $order, string $cancelReason)
    {
        $this->order = $order;
        $this->cancelReason = $cancelReason;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $itemsText = '';
        foreach ($this->order->items as $item) {
            $itemsText .= '• ' . $item->product_name . ' (Qty: ' . $item->quantity . ' x Rp ' . number_format($item->price, 0, ',', '.') . ')' . "\n";
        }

        return (new MailMessage)
            ->subject('🚫 Pesanan Dibatalkan - ' . $this->order->order_number)
            ->line('Halo Admin!')
            ->line('Sebuah pesanan telah dibatalkan oleh customer.')
            ->line('')
            ->line('**Detail Pesanan:**')
            ->line('No. Pesanan: ' . $this->order->order_number)
            ->line('Customer: ' . $this->order->user->name)
            ->line('Email: ' . $this->order->user->email)
            ->line('Total: Rp ' . number_format($this->order->total_amount, 0, ',', '.'))
            ->line('Tanggal Pembatalan: ' . $this->order->cancelled_at->format('d M Y H:i'))
            ->line('')
            ->line('**Alasan Pembatalan:**')
            ->line($this->cancelReason)
            ->line('')
            ->line('**Item yang Dibatalkan:**')
            ->line($itemsText)
            ->line('Silakan lakukan tindakan yang diperlukan.')
            ->line('Terima kasih!');
    }
}