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
                TextColumn::make('dun.Nama_DUN')
                    ->searchable(),
                TextColumn::make('daerah.Nama_Daerah')
                    ->searchable(),
                TextColumn::make('lokaliti.Nama_Lokaliti')->label('Lokaliti'),
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
