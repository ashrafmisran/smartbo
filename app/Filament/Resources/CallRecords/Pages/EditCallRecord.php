<?php

namespace App\Filament\Resources\CallRecords\Pages;

use App\Filament\Resources\CallRecords\CallRecordResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCallRecord extends EditRecord
{
    protected static string $resource = CallRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
