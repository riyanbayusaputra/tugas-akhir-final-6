<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Omset Harian';
    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $data = Order::query()
            ->where('payment_status', 'paid')
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Omset',
                    'data' => $data->pluck('total')->toArray(),
                    'fill' => false,
                    'borderColor' => '#4CAF50',
                    'tension' => 0.3,          // agar garis lebih smooth
                    'pointRadius' => 5,       // ukuran titik data
                    'pointHoverRadius' => 7,  // ukuran titik saat hover
                ],
            ],
            'labels' => $data->pluck('date')
                ->map(fn($date) => Carbon::parse($date)->format('d M Y'))
                ->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
