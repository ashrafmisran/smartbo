<?php

namespace App\Filament\Resources\Duns\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Table;
use Illuminate\Database\Query\Builder;

class DunsTable
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
                TextColumn::make('Kod_DUN')
                    ->label('Kod')
                    ->searchable(),
                TextColumn::make('Nama_DUN')
                    ->label('Nama DUN')
                    ->searchable()
                    ->summarize(Count::make()->label('Jumlah DUN')),
                TextColumn::make('daerahs_count')
                    ->label('Bil. Daerah')
                    ->badge()
                    ->searchable(false)
                    ->summarize(
                        Summarizer::make()
                            ->label('Jumlah Daerah')
                            ->using(fn (Builder $query) => \App\Models\Daerah::count())
                    ),
                TextColumn::make('lokalitis_count')
                    ->label('Bil. Lokaliti')
                    ->badge()
                    ->searchable(false)
                    ->summarize(
                        Summarizer::make()
                            ->label('Jumlah Lokaliti')
                            ->using(fn (Builder $query) => \App\Models\Lokaliti::count())
                    ),
                TextColumn::make('pengundis_count')
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
