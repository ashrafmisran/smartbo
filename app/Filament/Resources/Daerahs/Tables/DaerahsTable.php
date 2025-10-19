<?php

namespace App\Filament\Resources\Daerahs\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Table;
use Illuminate\Database\Query\Builder;

class DaerahsTable
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
                TextColumn::make('Kod_Daerah')
                    ->label('Kod')
                    ->searchable(),
                TextColumn::make('Nama_Daerah')
                    ->label('Nama Daerah')
                    ->searchable()
                    ->summarize(Count::make()->label('Jumlah Daerah')),
                TextColumn::make('lokalitis_count')
                    ->label('Bil. Lokaliti')
                    ->badge()
                    ->searchable(false)
                    ->summarize(
                        Summarizer::make()
                            ->label('Jumlah Lokaliti')
                            ->using(fn (Builder $query) => \App\Models\Lokaliti::count())
                    ),
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
