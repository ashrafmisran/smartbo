<?php

namespace App\Filament\Resources\Pengundis\Pages;

use App\Filament\Concerns\HasTelecallAction;
use App\Filament\Resources\Pengundis\PengundiResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPengundi extends ViewRecord
{
    use HasTelecallAction;
    
    protected static string $resource = PengundiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getTelecallAction(),
            EditAction::make(),
        ];
    }

    protected function resolveRecord($key): \Illuminate\Database\Eloquent\Model
    {
        // Handle invalid keys from null primary keys
        if ($key === 'invalid' || empty($key)) {
            abort(404);
        }
        
        // Since there's no actual primary key, we need to find by No_KP_Baru
        $record = static::getResource()::getEloquentQuery()
            ->where('No_KP_Baru', $key)
            ->first();
            
        if (!$record) {
            abort(404);
        }
        
        return $record;
    }
}
