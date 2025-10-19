<?php

namespace App\Filament\Resources\Daerahs\Pages;

use App\Filament\Resources\Daerahs\DaerahResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDaerah extends ViewRecord
{
    protected static string $resource = DaerahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
