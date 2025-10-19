<?php

namespace App\Filament\Resources\Lokalitis\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Table;
use Illuminate\Database\Query\Builder;

class LokalitisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('negeri.Nama_Negeri')
                    ->label('Negeri')
                    ->searchable(),
                TextColumn::make('parlimen.Nama_Parlimen')
                    ->label('Parlimen')
                    ->searchable(),
                TextColumn::make('dun.Nama_DUN')
                    ->label('DUN')
                    ->searchable(),
                TextColumn::make('daerah.Nama_Daerah')
                    ->label('Daerah')
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        $daerah = $record->getDaerah()->first();
                        return $daerah ? $daerah->Nama_Daerah : '-';
                    }),
                TextColumn::make('Kod_Lokaliti')
                    ->label('Kod')
                    ->searchable(),
                TextColumn::make('Nama_Lokaliti')
                    ->label('Nama Lokaliti')
                    ->searchable()
                    ->summarize(Count::make()->label('Jumlah Lokaliti')),
                TextColumn::make('pengundi_count')
                    ->label('Bil. Pengundi')
                    ->badge()
                    ->searchable(false)
                    ->summarize(
                        Summarizer::make()
                            ->label('Jumlah Pengundi')
                            ->using(fn (Builder $query) => \App\Models\Pengundi::count())
                    ),
            ])
            ->filters([
                //
            ]);
    }
}
