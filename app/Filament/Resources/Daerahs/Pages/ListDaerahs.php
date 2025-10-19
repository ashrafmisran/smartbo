<?php

namespace App\Filament\Resources\Daerahs\Pages;

use App\Filament\Resources\Daerahs\DaerahResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDaerahs extends ListRecords
{
    protected static string $resource = DaerahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
