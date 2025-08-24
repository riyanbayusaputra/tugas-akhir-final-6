<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StoreResource\Pages;
use App\Models\Store;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Services\BiteshipService;
use Filament\Forms\Components\Wizard;
use Filament\Notifications\Notification;

class StoreResource extends Resource
{
   protected static ?string $model = Store::class;
   protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
   protected static ?string $navigationGroup = 'Settings';
   protected static ?int $navigationSort = 2;

   public static function form(Form $form): Form
   {
       return $form
            ->schema([
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                        Forms\Components\TextInput::make('whatsapp')
                            ->prefix('62')
                            ->helperText('Mohon masukan nomor tanpa angka 0 diawal. Contoh 812345678900')
                            ->placeholder('812345678900')
                            ->required()
                            ->numeric()
                            ->dehydrateStateUsing(fn ($state) => '62' . ltrim($state, '62'))
                            ->formatStateUsing(fn ($state) => ltrim($state, '62'))
                            ->validationAttribute('Nomor WhatsApp')
                            ->maxLength(255),
                    Forms\Components\Textarea::make('description')
                        ->columnSpanFull(),
                ])->columns(2),
                Forms\Components\Card::make([
                    Forms\Components\Group::make([
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->directory('stores'),
                        Forms\Components\FileUpload::make('banner')
                            ->directory('stores/banner'),
                    ])->columns(2),
                    Forms\Components\Group::make([
                        Forms\Components\ColorPicker::make('primary_color'),
                        Forms\Components\ColorPicker::make('secondary_color'),
                        Forms\Components\Toggle::make('is_use_payment_gateway')
                            ->label('Aktifkan Payment Gateway'),
                    ])->columns(1),
                ])->columns(2),
                Forms\Components\Card::make([
                    Forms\Components\TextInput::make('email_notification')
                        ->email()
                        ->required()
                        ->maxLength(255),
                
                ])->columns(2),
           ]);
   }
   
   public static function table(Table $table): Table
   {
       return $table
           ->columns([
               Tables\Columns\TextColumn::make('name')
                   ->searchable(),
               Tables\Columns\ImageColumn::make('image')->circular(),
               Tables\Columns\TextColumn::make('whatsapp')
                   ->searchable(),
               Tables\Columns\ToggleColumn::make('is_use_payment_gateway')
                   ->label('Aktifkan Payment Gateway'),
               Tables\Columns\TextColumn::make('created_at')
                   ->dateTime()
                   ->sortable()
                   ->toggleable(isToggledHiddenByDefault: true),
               Tables\Columns\TextColumn::make('updated_at')
                   ->dateTime()
                   ->sortable()
                   ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('email_notification')
                   ->label('Email Notification')
                   ->searchable()
                   ->toggleable(isToggledHiddenByDefault: true),
           ])
           ->filters([])
           ->actions([
               Tables\Actions\EditAction::make(),
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
           'index' => Pages\ListStores::route('/'),
           'create' => Pages\CreateStore::route('/create'),
           'edit' => Pages\EditStore::route('/{record}/edit'),
       ];
   }

   public static function canCreate(): bool
   {
       return Store::count() < 1;
   }
}