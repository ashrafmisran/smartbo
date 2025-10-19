<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
