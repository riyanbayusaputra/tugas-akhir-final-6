<?php

namespace App\Filament\Resources\DeliveryAreaResource\Pages;

use App\Filament\Resources\DeliveryAreaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryAreas extends ListRecords
{
    protected static string $resource = DeliveryAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
