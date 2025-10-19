<?php

namespace App\Filament\Resources\Lokalitis\Pages;

use App\Filament\Resources\Lokalitis\LokalitiResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLokaliti extends ViewRecord
{
    protected static string $resource = LokalitiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
