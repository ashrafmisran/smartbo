<?php

namespace App\Filament\Resources\Negeris\Pages;

use App\Filament\Resources\Negeris\NegeriResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditNegeri extends EditRecord
{
    protected static string $resource = NegeriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
