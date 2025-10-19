<?php

namespace App\Filament\Resources\Duns\Pages;

use App\Filament\Resources\Duns\DunResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDun extends EditRecord
{
    protected static string $resource = DunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
