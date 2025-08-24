<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderStatusService;
use App\Notifications\PaymentReminderEmail;
use Illuminate\Console\Command;

class SendDailyReminders extends Command
{
    protected $signature = 'email:send-reminders';
    protected $description = 'Kirim reminder untuk order yang belum bayar';

    public function handle()
    {
        // Get unpaid orders that are 1-3 days old
        $orders = Order::where('payment_status', OrderStatusService::PAYMENT_UNPAID)
            ->whereBetween('created_at', [
                now()->subDays(3),
                now()->subDay()
            ])
            ->get();

        foreach ($orders as $order) {
            $order->user->notify(new PaymentReminderEmail($order));
        }

        $this->info('Reminder dikirim untuk ' . $orders->count() . ' orders');
        return 0;
    }
}