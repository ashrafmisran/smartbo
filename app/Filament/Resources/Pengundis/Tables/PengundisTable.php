<?php

namespace App\Filament\Resources\Pengundis\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class PengundisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('dun.Nama_DUN')
                    ->searchable(),
                TextColumn::make('nama_daerah')
                    ->label('Daerah')
                    ->sortable(query: function ($query, string $direction): void {
                        $query->leftJoin('daerah', function ($join) {
                            $join->whereRaw('daerah.Kod_DUN = LPAD(daftara.Kod_DUN, 2, "0")')
                                 ->whereRaw('daerah.Kod_Daerah = LPAD(daftara.Kod_Daerah, 2, "0")');
                        })->orderBy('daerah.Nama_Daerah', $direction);
                    })
                    ->searchable(false)
                    ->getStateUsing(function ($record) {
                        $daerah = \App\Models\Daerah::where('Kod_DUN', str_pad($record->Kod_DUN, 2, '0', STR_PAD_LEFT))
                            ->where('Kod_Daerah', str_pad($record->Kod_Daerah, 2, '0', STR_PAD_LEFT))
                            ->first();
                        return $daerah ? $daerah->Nama_Daerah : '-';
                    }),
                TextColumn::make('nama_lokaliti')
                    ->label('Lokaliti')
                    ->sortable(query: function ($query, string $direction): void {
                        $query->leftJoin('lokaliti', function ($join) {
                            $join->whereRaw('lokaliti.Kod_DUN = LPAD(daftara.Kod_DUN, 2, "0")')
                                 ->whereRaw('lokaliti.Kod_Daerah = LPAD(daftara.Kod_Daerah, 2, "0")')
                                 ->whereRaw('lokaliti.Kod_Lokaliti = LPAD(daftara.Kod_Lokaliti, 3, "0")');
                        })->orderBy('lokaliti.Nama_Lokaliti', $direction);
                    })
                    ->searchable(false)
                    ->getStateUsing(function ($record) {
                        $lokaliti = \App\Models\Lokaliti::where('Kod_DUN', str_pad($record->Kod_DUN, 2, '0', STR_PAD_LEFT))
                            ->where('Kod_Daerah', str_pad($record->Kod_Daerah, 2, '0', STR_PAD_LEFT))
                            ->where('Kod_Lokaliti', str_pad($record->Kod_Lokaliti, 3, '0', STR_PAD_LEFT))
                            ->first();
                        return $lokaliti ? $lokaliti->Nama_Lokaliti : '-';
                    }),
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
