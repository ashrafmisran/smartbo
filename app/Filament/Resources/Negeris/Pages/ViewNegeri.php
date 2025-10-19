<?php

namespace App\Filament\Resources\Negeris\Pages;

use App\Filament\Resources\Negeris\NegeriResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewNegeri extends ViewRecord
{
    protected static string $resource = NegeriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
