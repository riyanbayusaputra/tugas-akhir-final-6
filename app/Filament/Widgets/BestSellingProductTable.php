<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class BestSellingProductTable extends BaseWidget
{
    protected static ?int $sort = 2;
    protected static ?string $heading = 'Produk Terlaris';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderItem::query()
                    ->select([
                        'product_id',
                        'product_name',
                        DB::raw('COUNT(DISTINCT order_id) as total_orders'),
                        DB::raw('SUM(quantity) as total_quantity'),
                        DB::raw('SUM(price * quantity) as total_revenue')
                    ])
                    ->whereHas('order', function($query) {
                        $query->where('payment_status', 'paid');
                    })
                    ->groupBy('product_id', 'product_name')
                    ->orderByDesc('total_quantity')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('product_name')
                    ->label('Nama Produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('Total Terjual')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Total Pendapatan')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->paginated(false);
    }

    public function getTableRecordKey(Model $record): string
    {
        return (string) $record->product_id;
    }
}

