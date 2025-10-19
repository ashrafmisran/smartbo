<?php

namespace App\Filament\Resources\Negeris\Pages;

use App\Filament\Resources\Negeris\NegeriResource;
use App\Filament\Resources\Pages\BaseEditRecord;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;

class PindaNegeri extends BaseEditRecord
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