<?php

namespace App\Filament\Resources\Negeris\Pages;

use App\Filament\Resources\Negeris\NegeriResource;
use App\Filament\Resources\Pages\BaseViewRecord;
use Filament\Actions\EditAction;

class PaparNegeri extends BaseViewRecord
{
    protected static string $resource = NegeriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}