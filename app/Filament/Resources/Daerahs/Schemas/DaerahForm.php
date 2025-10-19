<?php

namespace App\Filament\Resources\Daerahs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DaerahForm
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
                TextInput::make('Nama_Daerah')
                    ->default(null),
                DateTimePicker::make('Date_Created'),
                TextInput::make('Created_By')
                    ->default(null),
                DateTimePicker::make('Last_Updated_Date'),
                TextInput::make('Last_Updated_By')
                    ->default(null),
            ]);
    }
}
