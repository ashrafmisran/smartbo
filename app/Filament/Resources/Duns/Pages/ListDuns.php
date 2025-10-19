<?php

namespace App\Filament\Resources\Duns\Pages;

use App\Filament\Resources\Duns\DunResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDuns extends ListRecords
{
    protected static string $resource = DunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
