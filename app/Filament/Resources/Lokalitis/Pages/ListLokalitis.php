<?php

namespace App\Filament\Resources\Lokalitis\Pages;

use App\Filament\Resources\Lokalitis\LokalitiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLokalitis extends ListRecords
{
    protected static string $resource = LokalitiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
