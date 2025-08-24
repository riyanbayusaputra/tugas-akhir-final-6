<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryAreaResource\Pages;
use App\Models\DeliveryArea;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use GuzzleHttp\Client;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Collection;

class DeliveryAreaResource extends Resource
{
    protected static ?string $model = DeliveryArea::class;
    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationLabel = 'Area Pengiriman';
    protected static ?string $modelLabel = 'Area Pengiriman';
    protected static ?string $pluralModelLabel = 'Area Pengiriman';
    protected static ?string $navigationGroup = 'Settings';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('provinsi_id')
                    ->label('Provinsi')
                    ->searchable()
                    ->options(function () {
                        return self::getProvinsiOptions();
                    })
                    ->afterStateUpdated(function (Set $set, $state) {
                        $set('kabupaten_id', null);
                        $set('kecamatan_id', null);
                        if ($state) {
                            $provinsi = self::getProvinsiById($state);
                            $set('provinsi_name', $provinsi['name'] ?? '');
                        }
                    })
                    ->live()
                    ->required(),

                Forms\Components\Hidden::make('provinsi_name'),

                Forms\Components\Select::make('kabupaten_id')
                    ->label('Kabupaten/Kota')
                    ->searchable()
                    ->options(function (Get $get) {
                        $provinsiId = $get('provinsi_id');
                        if (!$provinsiId) {
                            return [];
                        }
                        return self::getKabupatenOptions($provinsiId);
                    })
                    ->afterStateUpdated(function (Set $set, $state, Get $get) {
                        $set('kecamatan_id', null);
                        if ($state) {
                            $provinsiId = $get('provinsi_id');
                            $kabupaten = self::getKabupatenById($provinsiId, $state);
                            $set('kabupaten_name', $kabupaten['name'] ?? '');
                        }
                    })
                    ->live()
                    ->required(),

                Forms\Components\Hidden::make('kabupaten_name'),
                Forms\Components\Select::make('kecamatan_id')
                    ->label('Kecamatan')
                    ->searchable()
                    ->options(function (Get $get) {
                        $kabupatenId = $get('kabupaten_id');
                        if (!$kabupatenId) {
                            return [];
                        }
                        return self::getKecamatanOptions($kabupatenId);
                    })
                    ->afterStateUpdated(function (Set $set, $state, Get $get) {
                        if ($state) {
                            $kabupatenId = $get('kabupaten_id');
                            $kecamatan = self::getKecamatanById($kabupatenId, $state);
                            $set('kecamatan_name', $kecamatan['name'] ?? '');
                        }
                    })
                    ->live()
                    ->required(),
                Forms\Components\Hidden::make('kecamatan_name'),


                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
                // Forms\Components\TextInput::make('shipping_cost')
                //     ->label('Biaya Pengiriman')
                //     ->numeric()
                //     ->prefix('Rp')
                //     ->default(0)
                //     ->required(),
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('provinsi_name')
                    ->label('Provinsi')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('kabupaten_name')
                    ->label('Kabupaten/Kota')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Status')
                    ->sortable(),

                Tables\Columns\TextColumn::make('kecamatan_name')
                    ->label('Kecamatan')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('provinsi_id')
                    ->label('Provinsi')
                    ->options(function () {
                        return DeliveryArea::select('provinsi_id', 'provinsi_name')
                            ->distinct()
                            ->pluck('provinsi_name', 'provinsi_id');
                    }),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliveryAreas::route('/'),
            'create' => Pages\CreateDeliveryArea::route('/create'),
            'edit' => Pages\EditDeliveryArea::route('/{record}/edit'),
        ];
    }

    // Helper methods untuk mengambil data dari API
    private static function getApiData($endpoint, $params = [])
    {
        try {
            $client = new Client();
            $response = $client->get("https://api.binderbyte.com/wilayah/{$endpoint}", [
                'query' => array_merge([
                    'api_key' => 'a83a97cb58d93379b17e61de25fd839ce33445f6db05572672bf99344e697c97'
                ], $params)
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            return $data['value'] ?? $data['data'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private static function getProvinsiOptions()
    {
        $provinsis = self::getApiData('provinsi');
        return collect($provinsis)->pluck('name', 'id')->toArray();
    }

    private static function getKabupatenOptions($provinsiId)
    {
        $kabupatens = self::getApiData('kabupaten', ['id_provinsi' => $provinsiId]);
        return collect($kabupatens)->pluck('name', 'id')->toArray();
    }

    private static function getKecamatanOptions($kabupatenId)
    {
        $kecamatans = self::getApiData('kecamatan', ['id_kabupaten' => $kabupatenId]);
        return collect($kecamatans)->pluck('name', 'id')->toArray();
    }

    private static function getProvinsiById($id)
    {
        $provinsis = self::getApiData('provinsi');
        return collect($provinsis)->firstWhere('id', $id) ?? [];
    }

    private static function getKabupatenById($provinsiId, $kabupatenId)
    {
        $kabupatens = self::getApiData('kabupaten', ['id_provinsi' => $provinsiId]);
        return collect($kabupatens)->firstWhere('id', $kabupatenId) ?? [];
    }

    private static function getKecamatanById($kabupatenId, $kecamatanId)
    {
        $kecamatans = self::getApiData('kecamatan', ['id_kabupaten' => $kabupatenId]);
        return collect($kecamatans)->firstWhere('id', $kecamatanId) ?? [];
    }
}