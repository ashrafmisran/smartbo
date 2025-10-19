<?php

namespace App\Filament\Resources\Pengundis\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PengundiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('Kod_Negeri')
                    ->default(null),
                TextInput::make('Kod_Parlimen')
                    ->default(null),
                TextInput::make('Kod_DUN')
                    ->default(null),
                TextInput::make('Kod_Daerah')
                    ->default(null),
                TextInput::make('Kod_Lokaliti')
                    ->default(null),
                TextInput::make('No_KP_Baru')
                    ->default(null),
                TextInput::make('Nama')
                    ->default(null),
                TextInput::make('Keturunan')
                    ->default(null),
                TextInput::make('Bangsa')
                    ->default(null),
                TextInput::make('Agama')
                    ->default(null),
                TextInput::make('Kod_Cula')
                    ->default(null),
                Textarea::make('Catatan')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
