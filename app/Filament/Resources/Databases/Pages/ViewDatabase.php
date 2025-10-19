<?php

namespace App\Filament\Resources\Databases\Pages;

use App\Filament\Resources\Databases\DatabaseResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDatabase extends ViewRecord
{
    protected static string $resource = DatabaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
