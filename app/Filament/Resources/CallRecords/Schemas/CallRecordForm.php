<?php

namespace App\Filament\Resources\CallRecords\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CallRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('pengundi_id')
                    ->required()
                    ->numeric(),
                TextInput::make('phone_number')
                    ->tel()
                    ->required()
                    ->numeric(),
                TextInput::make('kod_cula'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
