<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderDeliveryConfirmedNotification extends Notification
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
        $itemsText = '';
        foreach ($this->order->items as $item) {
            $itemsText .= '• ' . $item->product_name . ' (Qty: ' . $item->quantity . ' x Rp ' . number_format($item->price, 0, ',', '.') . ')' . "\n";
        }

        return (new MailMessage)
            ->subject('✅ Konfirmasi Kedatangan - ' . $this->order->order_number)
            ->line('Halo Admin!')
            ->line('Customer telah mengkonfirmasi bahwa pesanan sudah diterima.')
            ->line('')
            ->line('**Detail Pesanan:**')
            ->line('No. Pesanan: ' . $this->order->order_number)
            ->line('Customer: ' . $this->order->user->name)
            ->line('Email: ' . $this->order->user->email)
            ->line('Total: Rp ' . number_format($this->order->total_amount, 0, ',', '.'))
        //    ->line('Tanggal Konfirmasi: ' . $this->order->confirmed_arrival_at?->format('d M Y H:i') ?? 'Belum dikonfirmasi')

            ->line('')
            ->line('**Alamat Pengiriman:**')
            ->line($this->order->recipient_name)
            ->line($this->order->shipping_address)
            ->line($this->order->provinsi_name . ', ' . $this->order->kabupaten_name . ', ' . $this->order->kecamatan_name)
     

            ->line('')
            ->line('**Item yang Dikirim:**')
            ->line($itemsText)
            ->line('Pesanan ini siap untuk diselesaikan.')
            ->line('Silakan ubah status menjadi "Completed" di dashboard.')
            ->line('Terima kasih!');
    }
}