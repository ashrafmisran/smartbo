<?php

namespace App\Filament\Resources\Pengundis\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PengundiInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('Kod_Negeri')
                    ->placeholder('-'),
                TextEntry::make('Kod_Parlimen')
                    ->placeholder('-'),
                TextEntry::make('Kod_DUN')
                    ->placeholder('-'),
                TextEntry::make('Kod_Daerah')
                    ->placeholder('-'),
                TextEntry::make('Kod_Lokaliti')
                    ->placeholder('-'),
                TextEntry::make('No_KP_Baru')
                    ->placeholder('-'),
                TextEntry::make('Nama')
                    ->placeholder('-'),
                TextEntry::make('Keturunan')
                    ->placeholder('-'),
                TextEntry::make('Bangsa')
                    ->placeholder('-'),
                TextEntry::make('Agama')
                    ->placeholder('-'),
                TextEntry::make('Kod_Cula')
                    ->placeholder('-'),
                TextEntry::make('Catatan')
                    ->placeholder('-')
                    ->columnSpanFull(),
            ]);
    }
}
