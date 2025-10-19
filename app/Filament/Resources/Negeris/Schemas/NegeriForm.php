<?php

namespace App\Filament\Resources\Negeris\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class NegeriForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('Kod_Negeri')
                    ->default(null),
                TextInput::make('Kod_Negeri_Lama')
                    ->default(null),
                TextInput::make('Nama_Negeri')
                    ->default(null),
                TextInput::make('Kod_Negeri_Lahir')
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
