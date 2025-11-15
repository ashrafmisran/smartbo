<?php

namespace App\Filament\Resources\CallRecords\Pages;

use App\Filament\Resources\CallRecords\CallRecordResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCallRecords extends ListRecords
{
    protected static string $resource = CallRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
