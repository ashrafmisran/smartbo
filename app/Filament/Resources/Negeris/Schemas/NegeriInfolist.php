<?php

namespace App\Filament\Resources\Negeris\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class NegeriInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('Kod_Negeri')
                    ->placeholder('-'),
                TextEntry::make('Kod_Negeri_Lama')
                    ->placeholder('-'),
                TextEntry::make('Nama_Negeri')
                    ->placeholder('-'),
                TextEntry::make('Kod_Negeri_Lahir')
                    ->placeholder('-'),
                TextEntry::make('Date_Created')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('Created_By')
                    ->placeholder('-'),
                TextEntry::make('Last_Updated_Date')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('Last_Updated_By')
                    ->placeholder('-'),
            ]);
    }
}
