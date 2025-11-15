<?php

namespace App\Filament\Resources\Pengundis\Pages;

use App\Filament\Resources\Pengundis\PengundiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ListPengundis extends ListRecords
{
    protected static string $resource = PengundiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public static function canAccess($parameters = []): bool
    {
        return auth()->user()->is_admin;
    }

    public function getTableRecordKey(Model|array $record): string
    {
        // Since there's no actual primary key, we use No_KP_Baru as unique identifier
        if (is_array($record)) {
            $key = $record['No_KP_Baru'] ?? null;
        } else {
            // Use No_KP_Baru directly since it's our unique identifier
            $key = $record->getAttribute('No_KP_Baru') ?? $record->getKey();
        }
        
        // If key is null or empty, this should not happen due to our query filter
        // But if it does, we'll handle it gracefully
        if (empty($key)) {
            // Log this occurrence for debugging
            Log::warning('Record with null/empty No_KP_Baru found in Pengundis table', [
                'record' => is_array($record) ? $record : $record->toArray()
            ]);
            return 'invalid';
        }
        
        return (string) $key;
    }
}
