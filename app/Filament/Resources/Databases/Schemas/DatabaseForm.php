<?php

namespace App\Filament\Resources\Databases\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DatabaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('host')
                    ->required(),
                TextInput::make('port')
                    ->required()
                    ->numeric()
                    ->default(3306),
                TextInput::make('username')
                    ->required(),
                TextInput::make('password')
                    ->password(),
                TextInput::make('alias'),
            ]);
    }
}
