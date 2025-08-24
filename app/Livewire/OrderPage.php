<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\Store;
use Livewire\Component;
use Livewire\WithPagination;
use App\Services\OrderStatusService;
use Illuminate\Support\Facades\Auth;

class OrderPage extends Component
{

    use WithPagination;

    public $search = '';
    public $store;

    public function mount(){
        $this->store = Store::first();
    }

    public function getStatusClass($status) 
    {
        return OrderStatusService::getStatusColor($status);
    }

    // public function getPaymentStatusLabel($status)
    // {
    //     return OrderStatusService::getPaymentStatusLabel($status);
    // }

    

    public function render()
    {
        return view('livewire.order', [
            'orders' => Order::with('items')
            ->where('user_id', Auth::user()->id)
            ->orderBy('updated_at', 'DESC')
            ->search($this->search) 
            ->paginate(3),

            // 'paymentStatusLabels' => array_combine(
            //     [
            //         OrderStatusService::PAYMENT_PAID,
            //         OrderStatusService::PAYMENT_UNPAID,

            //     ],
            //     array_map(
            //         fn($status) => OrderStatusService::getPaymentStatusLabel($status),
            //         [
            //             OrderStatusService::PAYMENT_PAID,
            //             OrderStatusService::PAYMENT_UNPAID,
            //         ]
            //     )
            // ),
            'statusLabels' => array_combine( //membuat array dengan status dan labelnya
                [
                    OrderStatusService::STATUS_CHECKING,
                    OrderStatusService::STATUS_PENDING,
                    OrderStatusService::STATUS_PROCESSING,
                    OrderStatusService::STATUS_SHIPPED,
                    OrderStatusService::STATUS_COMPLETED,
                    OrderStatusService::STATUS_CANCELLED
                ],
                array_map(
                    fn($status) => OrderStatusService::getStatusLabel($status),
                    [
                        OrderStatusService::STATUS_CHECKING,
                        OrderStatusService::STATUS_PENDING,
                        OrderStatusService::STATUS_PROCESSING,
                        OrderStatusService::STATUS_SHIPPED,
                        OrderStatusService::STATUS_COMPLETED,
                        OrderStatusService::STATUS_CANCELLED
                    ]
                )

            )
        ]);
    }
}
