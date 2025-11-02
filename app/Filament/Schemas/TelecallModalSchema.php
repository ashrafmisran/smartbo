<?php

namespace App\Filament\Schemas;

use App\Models\Pengundi;
use App\Services\TelecallService;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class TelecallModalSchema
{
    /**
     * Get the telecall modal schema
     */
    public static function getSchema(): array
    {
        return [
            Grid::make()
                ->schema([
                    Section::make('Maklumat Pengundi')
                        ->schema([
                            TextEntry::make('Nama')
                                ->label('Nama Pengundi')
                                ->default(fn (Pengundi $record) => $record->Nama),
                            TextEntry::make('phone_numbers')
                                ->label('Nombor Telefon')
                                ->default(fn (Pengundi $record) => TelecallService::getPhoneNumbers($record))
                                ->bulleted(),
                        ])
                        ->columnSpan(4),
                        
                    Section::make('')
                        ->extraAttributes([
                            'class' => 'max-h-4 overflow-y-auto p-4 border rounded-lg',
                        ])
                        ->schema([
                            TextEntry::make('skrip')
                                ->label('Skrip Panggilan')
                                ->html()
                                ->default(TelecallService::getSkripPanggilan()),
                        ])
                        ->columnSpan(6),
                        
                    Section::make('Rekod panggilan ini')
                        ->schema([
                            ToggleButtons::make('cula_pn')
                                ->label('Cula PN')
                                ->options([
                                    'VB' => 'PAS',
                                    'VC' => 'Condong PAS',
                                    'VS' => 'PN',
                                    'VT' => 'Rakan PN',
                                ])
                                ->grouped()
                                ->live(debounce: 0)
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $set('cula_lawan', null);
                                        $set('cula_lain', null);
                                    }
                                }),
                                
                            ToggleButtons::make('cula_lawan')
                                ->label('Cula Lawan')
                                ->options([
                                    'VD' => 'BN',
                                    'VN' => 'PH',
                                    'VR' => 'GRS',
                                ])
                                ->grouped()
                                ->live(debounce: 0)
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $set('cula_pn', null);
                                        $set('cula_lain', null);
                                    }
                                }),
                                
                            ToggleButtons::make('cula_lain')
                                ->label('Cula Lain')
                                ->options([
                                    'VA' => 'Atas Pagar',
                                    'VW' => 'Salah nombor',
                                    'VX' => 'Tidak angkat',
                                    'VY' => 'Tidak bersedia untuk jawab',
                                    'VZ' => 'Anti politik',
                                ])
                                ->live(debounce: 0)
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $set('cula_pn', null);
                                        $set('cula_lawan', null);
                                    }
                                }),
                        ])
                        ->columnSpan(5)
                ])
                ->columns(15),
        ];
    }

    /**
     * Get the telecall options for cula_pn
     */
    public static function getCulaPnOptions(): array
    {
        return [
            'VB' => 'PAS',
            'VC' => 'Condong PAS',
            'VS' => 'PN',
            'VT' => 'Rakan PN',
        ];
    }

    /**
     * Get the telecall options for cula_lawan
     */
    public static function getCulaLawanOptions(): array
    {
        return [
            'VD' => 'BN',
            'VN' => 'PH',
            'VR' => 'GRS',
        ];
    }

    /**
     * Get the telecall options for cula_lain
     */
    public static function getCulaLainOptions(): array
    {
        return [
            'VA' => 'Atas Pagar',
            'VW' => 'Salah nombor',
            'VX' => 'Tidak angkat',
            'VY' => 'Tidak bersedia untuk jawab',
            'VZ' => 'Anti politik',
        ];
    }
}
