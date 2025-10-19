<?php

namespace App\Filament\Resources\Pengundis\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PengundisTable
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
                    ->searchable(),
                TextColumn::make('Kod_Daerah')
                    ->searchable(),
                TextColumn::make('Kod_Lokaliti')
                    ->searchable(),
                TextColumn::make('No_KP_Baru')
                    ->searchable(),
                TextColumn::make('Nama')
                    ->searchable(),
                TextColumn::make('Keturunan')
                    ->searchable(),
                TextColumn::make('Bangsa')
                    ->searchable(),
                TextColumn::make('Agama')
                    ->searchable(),
                TextColumn::make('Kod_Cula')
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
