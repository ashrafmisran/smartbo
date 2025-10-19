<?php

namespace App\Filament\Resources\Databases\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DatabaseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('host'),
                TextEntry::make('port')
                    ->numeric(),
                TextEntry::make('username'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('alias')
                    ->placeholder('-'),
            ]);
    }
}
