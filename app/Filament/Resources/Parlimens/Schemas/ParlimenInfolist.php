<?php

namespace App\Filament\Resources\Parlimens\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ParlimenInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('Kod_Negeri')
                    ->placeholder('-'),
                TextEntry::make('Kod_Parlimen')
                    ->placeholder('-'),
                TextEntry::make('Nama_Parlimen')
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
