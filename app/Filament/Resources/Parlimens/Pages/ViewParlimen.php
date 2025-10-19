<?php

namespace App\Filament\Resources\Parlimens\Pages;

use App\Filament\Resources\Parlimens\ParlimenResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewParlimen extends ViewRecord
{
    protected static string $resource = ParlimenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
