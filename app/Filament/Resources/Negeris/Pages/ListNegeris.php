<?php

namespace App\Filament\Resources\Negeris\Pages;

use App\Filament\Resources\Negeris\NegeriResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNegeris extends ListRecords
{
    protected static string $resource = NegeriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
