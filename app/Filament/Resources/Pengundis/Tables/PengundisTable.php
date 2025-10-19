<?php

namespace App\Filament\Resources\Pengundis\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class PengundisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_daerah')
                    ->label('Negeri')
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        $daerah = $record->getDaerah();
                        return $daerah && $daerah->negeri ? $daerah->negeri->Nama_Negeri : '-';
                    }),
                TextColumn::make('nama_parlimen')
                    ->label('Parlimen')
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        $daerah = $record->getDaerah();
                        return $daerah && $daerah->parlimen ? $daerah->parlimen->Nama_Parlimen : '-';
                    }),
                TextColumn::make('nama_dun')
                    ->label('DUN')
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        $daerah = $record->getDaerah();
                        return $daerah && $daerah->dun ? $daerah->dun->Nama_DUN : '-';
                    }),
                TextColumn::make('nama_daerah')
                    ->label('Daerah')
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        return $record->nama_daerah ?: '-';
                    }),
                TextColumn::make('nama_lokalita')
                    ->label('Lokaliti')
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        return $record->nama_lokalita ?: '-';
                    }),
                TextColumn::make('No_KP_Baru')
                    ->searchable()
                    ->summarize(Count::make()->label('Jumlah Pengundi')),
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
            ]);
    }
}
