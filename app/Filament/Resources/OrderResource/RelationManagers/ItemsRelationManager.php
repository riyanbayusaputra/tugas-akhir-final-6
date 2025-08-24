<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('product_name')
                    ->required(),
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->required(),
               Forms\Components\RichEditor::make('product_description')
                ->label('Product Description')
    
                ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->numeric()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                Tables\Columns\ImageColumn::make('product.first_image_url')
                    ->label('Gambar Produk'),
                Tables\Columns\TextColumn::make('product_name')
                    ->label('Nama Produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga satuan')
                    ->formatStateUsing(fn ($state) => (string) ((int) $state)),
                Tables\Columns\TextColumn::make('product_description')
                    ->label('Deskripsi Produk')
                    ->html()
                    ->limit(50)
                    ->searchable(), 
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Jumlah'),
            ])
            ->filters([
                // Add filters if needed
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
             
            ])
            ->bulkActions([
          
            ]);
    }
}
