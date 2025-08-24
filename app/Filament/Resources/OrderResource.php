<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use App\Models\Store;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\ProductOptionItem;
use App\Services\MidtransService;
use Barryvdh\DomPDF\Facade as PDF;
use App\Services\OrderStatusService;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Actions\Action;
use App\Filament\Resources\OrderResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OrderResource\RelationManagers;

/**
 * OrderResource - Resource untuk mengelola pesanan dengan custom menu
 * Resource ini mengatur tampilan dan pengelolaan data pesanan
 * di dalam admin panel Filament dengan fitur custom options
 */
class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?int $navigationSort = 6;
    protected static ?string $navigationLabel = 'Orders';
    protected static ?string $pluralModelLabel = 'Orders';

    /**
     * Form untuk create/edit pesanan
     */
    public static function form(Form $form): Form
    {
        return $form->schema([
            // === KOLOM KIRI ===
            Forms\Components\Group::make()->schema([
                // --- SECTION: INFORMASI UMUM ---
                Forms\Components\Section::make('Informasi Umum')
                    ->description('Data dasar pesanan')
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->label('No. Pesanan')
                            ->disabled()
                            ->helperText('Nomor pesanan otomatis dari sistem'),

                        Forms\Components\TextInput::make('created_at')
                            ->label('Tanggal Pesan')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => Carbon::parse($state)->format('d M Y H:i'))
                            ->helperText('Kapan pesanan dibuat'),

                        Forms\Components\Select::make('user_id')
                            ->label('Pemesan')
                            ->relationship('user', 'name')
                            ->disabled()
                            ->helperText('User yang melakukan pemesanan'),
                    ]),

                // --- SECTION: INFORMASI USER ---
                Forms\Components\Section::make('Informasi User')
                    ->description('Data lengkap user pemesan')
                    ->schema([
                        Forms\Components\TextInput::make('user.email')
                            ->label('Email User')
                            ->formatStateUsing(fn ($record) => $record?->user?->email ?? '-')
                            ->disabled()
                            ->helperText('Email user yang terdaftar'),

                        Forms\Components\TextInput::make('user.name')
                            ->label('Nama User')
                            ->formatStateUsing(fn ($record) => $record?->user?->name ?? '-')
                            ->disabled()
                            ->helperText('Nama lengkap user'),
                    ]),

                // --- SECTION: DATA PENERIMA ---
                Forms\Components\Section::make('Data Penerima')
                    ->description('Informasi lengkap penerima barang')
                    ->schema([
                        Forms\Components\TextInput::make('recipient_name')
                            ->label('Nama Penerima')
                            ->disabled()
                            ->helperText('Nama yang akan menerima barang'),

                        Forms\Components\TextInput::make('phone')
                            ->label('No. Telepon')
                            ->tel()
                            ->disabled()
                            ->helperText('Nomor telepon yang bisa dihubungi'),

                        Forms\Components\RichEditor::make('shipping_address')
                            ->label('Alamat Pengiriman')
                            ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link'])
                            ->disabled()
                            ->helperText('Alamat lengkap untuk pengiriman'),

                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('provinsi_name')
                                ->label('Provinsi')
                                ->disabled(),
                            Forms\Components\TextInput::make('kabupaten_name')
                                ->label('Kabupaten')
                                ->disabled(),
                            Forms\Components\TextInput::make('kecamatan_name')
                                ->label('Kecamatan')
                                ->disabled(),
                        ]),

                        Forms\Components\RichEditor::make('noted')
                            ->label('Catatan Khusus')
                            ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link'])
                            ->disabled()
                            ->helperText('Catatan khusus dari customer'),

                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('delivery_date')
                                ->label('Tanggal Pengiriman')
                                ->disabled()
                                ->helperText('Tanggal yang diinginkan'),
                            Forms\Components\TextInput::make('delivery_time')
                                ->label('Waktu Pengiriman')
                                ->disabled()
                                ->helperText('Waktu pengiriman yang diinginkan'),
                        ]),
                    ]),

                // --- SECTION: PRODUK YANG DIPESAN ---
                Forms\Components\Section::make('Produk yang Dipesan')
                    ->description('Daftar produk dalam pesanan ini')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->label('Item Pesanan')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('product_name')
                                    ->label('Nama Produk')
                                    ->disabled()
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->disabled()
                                    ->numeric()
                                    ->suffix('pcs'),
                                Forms\Components\TextInput::make('price')
                                    ->label('Harga Total')
                                    ->disabled()
                                    ->numeric()
                                    ->prefix('Rp ')
                                    ->formatStateUsing(fn ($state) => number_format($state ?? 0, 0, ',', '.')),
                                    
                                // Tambahan untuk menampilkan custom options
                                Forms\Components\Section::make('Custom Options')
                                    ->schema([
                                        Forms\Components\Placeholder::make('custom_options_display')
                                            ->label('Detail Pilihan Custom')
                                            ->content(function ($record) {
                                                if (!$record || !$record->custom_options_json) {
                                                    return 'Tidak ada custom options';
                                                }
                                                
                                                $html = '<div class="space-y-2">';
                                                $customOptions = json_decode($record->custom_options_json, true);
                                                
                                                if ($customOptions) {
                                                    foreach ($customOptions as $optionId => $items) {
                                                        // Ambil nama option
                                                        $option = \App\Models\ProductOption::find($optionId);
                                                        if ($option) {
                                                            $html .= '<div class="border-l-4 border-blue-500 pl-3 mb-2">';
                                                            $html .= '<h4 class="font-semibold text-gray-800">' . $option->name . '</h4>';
                                                                                                                  
    
                                                            
                                                            foreach ($items as $itemId => $quantity) {
                                                                if ($quantity > 0) {
                                                                    $item = ProductOptionItem::find($itemId);
                                                                    if ($item) {
                                                                        $subtotal = $item->additional_price * $quantity;
                                                                        $html .= '<div class="flex justify-between items-center py-1 text-sm">';
                                                                        $html .= '<span>' . $item->name . ' × ' . $quantity . '</span>';
                                                                        $html .= '<span class="font-medium">+Rp ' . number_format($subtotal, 0, ',', '.') . '</span>';
                                                                        $html .= '</div>';
                                                                    }
                                                                }
                                                            }
                                                            $html .= '</div>';
                                                        }
                                                    }
                                                }
                                                $html .= '</div>';
                                                
                                                return new \Illuminate\Support\HtmlString($html);
                                            })
                                            ->columnSpan(4),
                                    ])
                                    ->collapsible()
                                    ->collapsed()
                                    ->visible(fn ($record) => $record && $record->custom_options_json),
                            ])
                            ->disabled()
                            ->columns(4)
                            ->columnSpan('full')
                            ->collapsible(),
                    ]),

                // --- SECTION: CUSTOM CATERING ---
                Forms\Components\Section::make('Custom Catering')
                    ->description('Detail tambahan pesanan catering')
                    ->schema([
                        Forms\Components\Repeater::make('customCatering')
                            ->label('Tambahan Pesanan')
                            ->relationship()
                            ->schema([
                                Forms\Components\Textarea::make('menu_description')
                                    ->label('Deskripsi')
                                    ->disabled()
                                    ->rows(2),
                            ])
                            ->disableItemCreation()
                            ->disableItemDeletion()
                            ->disabled()
                            ->collapsible(),
                    ])
                    ->visible(fn ($record) => $record?->is_custom_catering == true),
            ])->columnSpan(['lg' => 2]),

            // === KOLOM KANAN ===
            Forms\Components\Group::make()->schema([
                // --- SECTION: DETAIL HARGA ---
                Forms\Components\Section::make('Detail Harga')
                    ->description('Rincian biaya pesanan')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->disabled()
                            ->numeric()
                            ->prefix('Rp ')
                            ->formatStateUsing(fn ($state) => number_format($state ?? 0, 0, ',', '.'))
                            ->helperText('Total harga'),

                        Forms\Components\TextInput::make('shipping_cost')
                            ->label('Biaya Pengiriman')
                            ->disabled(fn (Forms\Get $get) => $get('payment_status') == OrderStatusService::PAYMENT_PAID)
                            ->numeric()
                            ->prefix('Rp ')
                            ->step(1000)
                            ->helperText('Ongkos kirim')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $subtotal = (float) ($get('subtotal') ?? 0);
                                $shippingCost = (float) ($state ?? 0);
                                $adjustment = (float) ($get('price_adjustment') ?? 0);
                            
                                $newTotal = $subtotal + $shippingCost + $adjustment;
                                $set('total_amount', $newTotal);
                            }),

                       Forms\Components\TextInput::make('price_adjustment')
                            ->label('Penyesuaian Harga Custom')
                            ->disabled(fn (Forms\Get $get) => !$get('is_custom_catering') || $get('payment_status') == OrderStatusService::PAYMENT_PAID)
                            ->numeric()
                            ->prefix('Rp ')
                            ->step(1000)
                            ->placeholder('0')
                            ->default(0)
                            ->helperText('Masukkan angka: positif untuk menambah harga, negatif untuk mengurangi. Contoh: 50000 atau -25000')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $subtotal = (float) ($get('subtotal') ?? 0);
                                $shippingCost = (float) ($get('shipping_cost') ?? 0);
                                $adjustment = (float) ($state ?? 0);
                            
                                $newTotal = $subtotal + $shippingCost + $adjustment;
                                $set('total_amount', $newTotal);
                            }),

                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total Pembayaran')
                            ->disabled()
                            ->numeric()
                            ->prefix('Rp ')
                            ->helperText('Total yang harus dibayar')
                            ->extraAttributes(['class' => 'font-bold text-lg'])
                            ->formatStateUsing(fn ($state) => number_format($state ?? 0, 0, ',', '.')),
                    ]),

                // --- SECTION: RINGKASAN CUSTOM OPTIONS ---
                Forms\Components\Section::make('Ringkasan Custom Options')
                    ->description('Total tambahan dari pilihan custom')
                    ->schema([
                        Forms\Components\Placeholder::make('custom_options_summary')
                            ->label('Total pilihan custom')
                            ->content(function ($record) {
                                if (!$record || !$record->items) {
                                    return 'Rp 0';
                                }
                                
                                $totalCustomOptions = 0;
                                foreach ($record->items as $item) {
                                    if ($item->custom_options_json) {
                                        $customOptions = json_decode($item->custom_options_json, true);
                                        if ($customOptions) {
                                            foreach ($customOptions as $optionId => $items) {
                                                foreach ($items as $itemId => $quantity) {
                                                    if ($quantity > 0) {
                                                        $optionItem = ProductOptionItem::find($itemId);
                                                        if ($optionItem) {
                                                            $totalCustomOptions += $optionItem->additional_price * $quantity * $item->quantity;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                
                                return 'Rp ' . number_format($totalCustomOptions, 0, ',', '.');
                            })
                            ->extraAttributes(['class' => 'text-lg font-semibold text-blue-600'])
                    ])
                    ->collapsible()
                    ->collapsed(),

                // --- SECTION: STATUS & PEMBAYARAN ---
                Forms\Components\Section::make('Status & Pembayaran')
                    ->description('Informasi pembayaran dan status pesanan')
                    ->schema([
                        Forms\Components\TextInput::make('payment_gateway_transaction_id')
                            ->label('URL Pembayaran Gateway')
                            ->disabled()
                            ->visible(fn ($record) => $record && !empty($record->payment_gateway_transaction_id))
                            ->helperText('Link pembayaran online'),

                        Forms\Components\FileUpload::make('payment_proof')
                            ->label('Bukti Pembayaran')
                            ->image()
                            ->disk('public')
                            ->directory('payment-proofs')
                            ->visible(fn ($record) => $record && !empty($record->payment_proof))
                            ->disabled()
                            ->helperText('Bukti transfer dari customer'),

                        Forms\Components\Select::make('payment_status')
                            ->label('Status Pembayaran')
                            ->options([
                                OrderStatusService::PAYMENT_UNPAID => 'Belum Dibayar',
                                OrderStatusService::PAYMENT_PAID => 'Sudah Dibayar',
                            ])
                            ->required()
                            ->live()
                            ->disabled(fn ($record) => $record?->snap_token != null)
                            ->helperText('Status pembayaran saat ini'),

                        Forms\Components\Select::make('status')
                            ->label('Status Pesanan')
                            ->options([
                                OrderStatusService::STATUS_CHECKING => 'Menunggu Konfirmasi',
                                OrderStatusService::STATUS_PENDING => 'Menunggu Pembayaran',
                                OrderStatusService::STATUS_PROCESSING => 'Sedang Diproses',
                                OrderStatusService::STATUS_SHIPPED => 'Sedang Dikirim',
                                OrderStatusService::STATUS_COMPLETED => 'Selesai',
                                OrderStatusService::STATUS_CANCELLED => 'Dibatalkan',
                            ])
                            ->required()
                            ->live()
                            ->helperText('Status pesanan saat ini'),
                    ]),

                // --- SECTION: INFORMASI PEMBATALAN (Hanya tampil jika dibatalkan) ---
                Forms\Components\Section::make('Informasi Pembatalan')
                    ->description('Detail pembatalan pesanan')
                    ->schema([
                        Forms\Components\TextInput::make('cancelled_at')
                            ->label('Waktu Pembatalan')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('d M Y H:i') : '-')
                            ->helperText('Kapan pesanan dibatalkan'),

                        Forms\Components\Select::make('cancelled_by')
                            ->label('Dibatalkan Oleh')
                            ->options([
                                'user' => 'Pelanggan',
                                'admin' => 'Admin',
                                'system' => 'Sistem',
                            ])
                            ->disabled()
                            ->helperText('Siapa yang membatalkan pesanan'),

                        Forms\Components\Textarea::make('cancel_reason')
                            ->label('Alasan Pembatalan')
                            ->disabled()
                            ->rows(3)
                            ->helperText('Alasan mengapa pesanan dibatalkan'),
                    ])
                    ->visible(fn ($record) => $record && $record->status === 'cancelled'),
            ])->columnSpan(['lg' => 1]),
        ])->columns(3);
    }

    /**
     * Tabel untuk menampilkan daftar pesanan
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('No. Pesanan')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->description(fn (Order $record): string => $record->created_at->format('d M Y (H:i)'))
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('recipient_name')
                    ->label('Penerima')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Order $record): string => $record->phone ?? '-')
                    ->limit(30),

                // Tambah kolom untuk menampilkan info custom options
                Tables\Columns\TextColumn::make('custom_items_count')
                    ->label('Custom Items')
                    ->getStateUsing(function (Order $record): string {
                        $customItemsCount = 0;
                        foreach ($record->items as $item) {
                            if ($item->custom_options_json) {
                                $customOptions = json_decode($item->custom_options_json, true);
                                if ($customOptions) {
                                    foreach ($customOptions as $optionId => $items) {
                                        foreach ($items as $itemId => $quantity) {
                                            if ($quantity > 0) {
                                                $customItemsCount += $quantity;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        return $customItemsCount > 0 ? $customItemsCount . ' items' : 'Tidak ada';
                    })
                    ->badge()
                    ->color(fn ($state): string => $state === 'Tidak ada' ? 'gray' : 'success'),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Harga')
                    ->formatStateUsing(fn (string $state): string => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable()
                    ->alignEnd()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Status Pembayaran')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        OrderStatusService::PAYMENT_UNPAID => 'danger',
                        OrderStatusService::PAYMENT_PAID => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        OrderStatusService::PAYMENT_UNPAID => 'BELUM BAYAR',
                        OrderStatusService::PAYMENT_PAID => 'SUDAH BAYAR',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status Pesanan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        OrderStatusService::STATUS_CHECKING => 'gray',
                        OrderStatusService::STATUS_PENDING => 'warning',
                        OrderStatusService::STATUS_PROCESSING => 'info',
                        OrderStatusService::STATUS_SHIPPED => 'primary',
                        OrderStatusService::STATUS_COMPLETED => 'success',
                        OrderStatusService::STATUS_CANCELLED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        OrderStatusService::STATUS_CHECKING => 'Menunggu Konfirmasi',
                        OrderStatusService::STATUS_PENDING => 'Menunggu Pembayaran',
                        OrderStatusService::STATUS_PROCESSING => 'Diproses',
                        OrderStatusService::STATUS_SHIPPED => 'Dikirim',
                        OrderStatusService::STATUS_COMPLETED => 'Selesai',
                        OrderStatusService::STATUS_CANCELLED => 'Dibatalkan',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        OrderStatusService::PAYMENT_UNPAID => 'Belum Dibayar',
                        OrderStatusService::PAYMENT_PAID => 'Sudah Dibayar',
                    ])
                    ->placeholder('Semua Status Pembayaran'),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Pesanan')
                    ->options([
                        OrderStatusService::STATUS_CHECKING => 'Menunggu Konfirmasi',
                        OrderStatusService::STATUS_PENDING => 'Menunggu Pembayaran',
                        OrderStatusService::STATUS_PROCESSING => 'Sedang Diproses',
                        OrderStatusService::STATUS_SHIPPED => 'Sedang Dikirim',
                        OrderStatusService::STATUS_COMPLETED => 'Selesai',
                        OrderStatusService::STATUS_CANCELLED => 'Dibatalkan',
                    ])
                    ->placeholder('Semua Status Pesanan'),

                // Filter untuk custom options
                Tables\Filters\Filter::make('has_custom_options')
                    ->label('Memiliki Custom Options')
                    ->query(fn (Builder $query): Builder => $query->whereHas('items', function ($q) {
                        $q->whereNotNull('custom_options_json');
                    }))
                    ->toggle(),

                // Tables\Filters\SelectFilter::make('cancelled_by')
                //     ->label('Dibatalkan Oleh')
                //     ->options([
                //         'user' => 'Pelanggan',
                //         'admin' => 'Admin',
                //         'system' => 'Sistem',
                //     ])
                //     ->placeholder('Semua')
                //     ->visible(fn () => true),

                Tables\Filters\Filter::make('created_at')
                    ->label('Tanggal Pesanan')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('created_until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                // Tables\Actions\Action::make('view_custom_options')
                //     ->label('Lihat Custom Options')
                //     ->icon('heroicon-o-eye')
                //     ->color('info')
                //     ->modalHeading('Detail Custom Options')
                //     ->modalContent(function (Order $record) {
                //         $content = '<div class="space-y-4">';
                        
                //         foreach ($record->items as $item) {
                //             $content .= '<div class="border rounded-lg p-4">';
                //             $content .= '<h3 class="font-semibold text-lg mb-2">' . $item->product_name . '</h3>';
                            
                //             if ($item->custom_options_json) {
                //                 $customOptions = json_decode($item->custom_options_json, true);
                                
                //                 if ($customOptions) {
                //                     foreach ($customOptions as $optionId => $items) {
                //                         $option = \App\Models\ProductOption::find($optionId);
                //                         if ($option) {
                //                             $content .= '<div class="mb-3">';
                //                             $content .= '<h4 class="font-medium text-gray-700 border-l-4 border-blue-500 pl-2">' . $option->name . '</h4>';
                                            
                //                             foreach ($items as $itemId => $quantity) {
                //                                 if ($quantity > 0) {
                //                                     $optionItem = ProductOptionItem::find($itemId);
                //                                     if ($optionItem) {
                //                                         $subtotal = $optionItem->additional_price * $quantity;
                //                                         $content .= '<div class="flex justify-between py-1 text-sm ml-4">';
                //                                         $content .= '<span>' . $optionItem->name . ' × ' . $quantity . '</span>';
                //                                         $content .= '<span class="font-medium">+Rp ' . number_format($subtotal, 0, ',', '.') . '</span>';
                //                                         $content .= '</div>';
                //                                     }
                //                                 }
                //                             }
                //                             $content .= '</div>';
                //                         }
                //                     }
                //                 } else {
                //                     $content .= '<p class="text-gray-500 italic">Tidak ada custom options</p>';
                //                 }
                //             } else {
                //                 $content .= '<p class="text-gray-500 italic">Tidak ada custom options</p>';
                //             }
                            
                //             $content .= '</div>';
                //         }
                        
                //         $content .= '</div>';
                        
                //         return new \Illuminate\Support\HtmlString($content);
                //     })
                //     ->visible(fn ($record) => $record->items->some(fn ($item) => !empty($item->custom_options_json))),

                Tables\Actions\Action::make('kirim_invoice')
                    ->label('Kirim Invoice')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->visible(fn ($record) => $record->payment_status == OrderStatusService::PAYMENT_PAID)
                    ->requiresConfirmation()
                    ->modalHeading('Kirim Invoice')
                    ->modalDescription('Invoice akan dikirim ke email customer')
                    ->modalSubmitActionLabel('Kirim')
                    ->action(function (Order $record) {
                        $record->user->notify(new \App\Notifications\InvoiceEmail($record));
                        \Filament\Notifications\Notification::make()
                            ->title('Invoice Berhasil Dikirim!')
                            ->body('Invoice telah dikirim ke email customer')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make()
                    ->label('Lihat')
                    ->color('info'),

                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->color('warning'),

                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->visible(fn ($record) => $record->status != OrderStatusService::STATUS_COMPLETED)
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Pesanan')
                    ->modalDescription('Apakah Anda yakin ingin menghapus pesanan ini?')
                    ->modalSubmitActionLabel('Ya, Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Pesanan Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus pesanan yang dipilih?'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s')
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession();
    }

    /**
     * Relasi yang bisa dikelola
     */
    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    /**
     * Halaman-halaman yang tersedia
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    /**
     * Badge untuk navigation (jumlah pesanan)
     */
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereNotIn('status', [
            OrderStatusService::STATUS_COMPLETED,
            OrderStatusService::STATUS_CANCELLED,
        ])->count();
    }

    /**
     * Warna badge navigation
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() > 50 ? 'warning' : 'primary';
    }

    /**
     * Method untuk menghitung total custom options dalam pesanan
     */
    public static function calculateCustomOptionsTotal(Order $order): int
    {
        $total = 0;
        
        foreach ($order->items as $item) {
            if ($item->custom_options_json) {
                $customOptions = json_decode($item->custom_options_json, true);
                
                if ($customOptions) {
                    foreach ($customOptions as $optionId => $items) {
                        foreach ($items as $itemId => $quantity) {
                            if ($quantity > 0) {
                                $optionItem = ProductOptionItem::find($itemId);
                                if ($optionItem) {
                                    $total += $optionItem->additional_price * $quantity * $item->quantity;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $total;
    }

    /**
     * Method untuk format custom options ke dalam bentuk text yang mudah dibaca
     */
    public static function formatCustomOptionsText(string $customOptionsJson): string
    {
        $customOptions = json_decode($customOptionsJson, true);
        $text = '';
        
        if ($customOptions) {
            foreach ($customOptions as $optionId => $items) {
                $option = \App\Models\ProductOption::find($optionId);
                if ($option) {
                    $text .= $option->name . ":\n";
                    
                    foreach ($items as $itemId => $quantity) {
                        if ($quantity > 0) {
                            $item = ProductOptionItem::find($itemId);
                            if ($item) {
                                $subtotal = $item->additional_price * $quantity;
                                $text .= "  - " . $item->name . " x" . $quantity;
                                if ($item->additional_price > 0) {
                                    $text .= " (+Rp " . number_format($subtotal, 0, ',', '.') . ")";
                                }
                                $text .= "\n";
                            }
                        }
                    }
                    $text .= "\n";
                }
            }
        }
        
        return trim($text);
    }
}