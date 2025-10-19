<?php

namespace App\Filament\Resources\Daerahs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DaerahsTable
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
                TextColumn::make('Kod_Daerah')
                    ->searchable(),
                TextColumn::make('Nama_Daerah')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
