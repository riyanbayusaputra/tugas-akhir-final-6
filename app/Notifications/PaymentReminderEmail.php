<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReminderEmail extends Notification
{
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Reminder Pembayaran - Order #' . $this->order->order_number)
            ->line('Halo ' . $this->order->user->name)
            ->line('Pesanan Anda nomor: ' . $this->order->order_number)
            ->line('Total: Rp ' . number_format($this->order->total_amount, 0, ',', '.'))
            ->line('Silakan segera lakukan pembayaran.')
            ->line('Terima kasih!');
    }
}