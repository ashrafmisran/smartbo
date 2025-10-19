<?php

namespace App\Filament\Resources\Duns\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DunsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('Kod_Negeri')
                    ->searchable(),
                TextColumn::make('Kod_Parlimen')
                    ->searchable(),
                TextColumn::make('Kod_DUN')
                    ->label('Kod DUN')
                    ->searchable(),
                TextColumn::make('Nama_DUN')
                    ->label('Nama DUN')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
