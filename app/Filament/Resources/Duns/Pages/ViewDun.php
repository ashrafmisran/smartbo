<?php

namespace App\Filament\Resources\Duns\Pages;

use App\Filament\Resources\Duns\DunResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDun extends ViewRecord
{
    protected static string $resource = DunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
