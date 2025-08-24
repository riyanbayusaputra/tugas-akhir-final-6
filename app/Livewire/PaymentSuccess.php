<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;
use App\Services\MidtransService;

class PaymentSuccess extends Component
{
    public $order;

    public function mount()
    {
        $orderNumber = request('order_id');
        $transactionStatus = request('transaction_status');
        $this->order = Order::where('order_number', $orderNumber)
        ->where('user_id', auth()->user()->id)
        ->firstOrFail();

        if ($this->order) {
            switch ($transactionStatus) {
                case 'settlement':
                    $this->order->update([
                        'payment_status' => 'paid',
                        'status' => 'processing'
                    ]);
                    break;
                case 'pending':
                    $this->order->update([
                        'payment_status' => 'unpaid',
                        'status' => 'pending'
                    ]);
                    break;
                case 'deny':
                case 'cancel':
                case 'expire':
                    $this->order->update([
                        'payment_status' => 'unpaid',
                        'status' => 'cancelled'
                    ]);
                    break;
            }
        }else {
            abort(404);
        }
    }

    function expire()
    {
        $orderId = request('order_id');
        $order = Order::where('order_number', $orderId)
        ->where('user_id', auth()->user()->id)
        ->first();
        $order->update([
            'payment_status' => 'unpaid',
            'status' => 'cancelled'
        ]);

        return redirect()->route('payment-success', ['order_id' => $order->order_number , 'transaction_status' => 'expire']);

    }


    public function render()
    {
        return view('livewire.payment-success')
        ->layout('components.layouts.app', ['hideBottomNav' => true]);
    }
}
