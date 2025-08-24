<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceEmail extends Notification
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
        // Generate PDF invoice
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('livewire.invoice', ['order' => $this->order]);
        
        return (new MailMessage)
            ->subject('Invoice - Order #' . $this->order->order_number)
            ->line('Terima kasih! Pembayaran sudah diterima.')
            ->line('Pesanan: ' . $this->order->order_number)
            ->line('Invoice terlampir.')
            ->attachData($pdf->output(), 'invoice-' . $this->order->order_number . '.pdf')
            ->line('Terima kasih!');
    }
}