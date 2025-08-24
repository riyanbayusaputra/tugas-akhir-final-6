<?php

namespace App\Filament\Resources\ProductOptionItemResource\Pages;

use App\Filament\Resources\ProductOptionItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductOptionItem extends EditRecord
{
    protected static string $resource = ProductOptionItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
