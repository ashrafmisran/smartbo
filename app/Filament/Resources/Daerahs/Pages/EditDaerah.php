<?php

namespace App\Filament\Resources\Daerahs\Pages;

use App\Filament\Resources\Daerahs\DaerahResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDaerah extends EditRecord
{
    protected static string $resource = DaerahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
