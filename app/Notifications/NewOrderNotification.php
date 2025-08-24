<?php
namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class NewOrderNotification extends Notification
{
    protected $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('New Order #' . $this->order->order_number)
                    ->greeting('Hello Admin !')
                    ->line('Ada pesanan baru dari '.$this->order->recipient_name)
                    ->line('Detail Pesanan :')
                    ->line('- Order Number : '. $this->order->order_number)
                    ->line('- Total Rp '. number_format($this->order->total_amount, 0, ',', '.'))
                    ->line('- Items:')
                    ->lines($this->order->items->map(function($item) {
                        return " {$item->product_name} ({$item->quantity}x)";
                    }));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}