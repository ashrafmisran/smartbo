<?php

namespace App\Filament\Resources\Lokalitis\Pages;

use App\Filament\Resources\Lokalitis\LokalitiResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLokaliti extends EditRecord
{
    protected static string $resource = LokalitiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
