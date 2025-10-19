<?php

namespace App\Filament\Resources\Negeris\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Table;
use Illuminate\Database\Query\Builder;

class NegerisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('Kod_Negeri')
                    ->label('Kod')
                    ->searchable(),
                TextColumn::make('Nama_Negeri')
                    ->label('Nama Negeri')
                    ->searchable()
                    ->summarize(Count::make()->label('Jumlah Negeri')),
                TextColumn::make('parlimens_count')
                    ->label('Bil. Parlimen')
                    ->badge()
                    ->searchable(false)
                    ->summarize(
                        Summarizer::make()
                            ->label('Jumlah Parlimen')
                            ->using(fn (Builder $query) => \App\Models\Parlimen::count())
                    ),
                TextColumn::make('duns_count')
                    ->label('Bil. DUN')
                    ->badge()
                    ->searchable(false)
                    ->summarize(
                        Summarizer::make()
                            ->label('Jumlah DUN')
                            ->using(fn (Builder $query) => \App\Models\Dun::count())
                    ),
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
            ])
            ->paginated(false);
    }
}
