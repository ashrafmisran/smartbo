<?php

namespace App\Filament\Resources\Parlimens\Pages;

use App\Filament\Resources\Parlimens\ParlimenResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditParlimen extends EditRecord
{
    protected static string $resource = ParlimenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
