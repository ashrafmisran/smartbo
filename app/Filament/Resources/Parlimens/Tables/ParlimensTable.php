<?php

namespace App\Filament\Resources\Parlimens\Tables;

use App\Models\Parlimen;
use App\Services\PengundiCountService;
use Illuminate\Support\Facades\Cache;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ParlimensTable
{
    public static function getTableQuery(): Builder
    {
        return Parlimen::query()
            ->select([
                'parlimen.REC_ID',
                'parlimen.Kod_Negeri',
                'parlimen.Kod_Parlimen',   // only the columns we display or need for relations
                'parlimen.Nama_Parlimen',
            ])
            ->with([
                // Eager-load negeri with only needed columns
                'negeri:Kod_Negeri,Nama_Negeri',
            ])
            ->withCount([
                'duns',
                'daerahs',
                'lokalitis',
                // omit pengundis here; use cached grouped counts instead
            ])
            ->orderBy('parlimen.Nama_Parlimen');
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->query(self::getTableQuery())
            ->columns([
                TextColumn::make('negeri.Nama_Negeri')
                    ->label('Negeri')
                    ->searchable(),
                TextColumn::make('Kod_Parlimen')
                    ->label('Kod')
                    ->searchable(),
                TextColumn::make('Nama_Parlimen')
                    ->label('Nama Parlimen')
                    ->searchable()
                    ->summarize(Count::make()->label('Jumlah Parlimen')),
                TextColumn::make('duns_count')
                    ->label('Bil. DUN')
                    ->badge()
                    ->searchable(false)
                    ->summarize(
                        Summarizer::make()
                            ->label('Jumlah DUN')
                            ->using(function ($query) {
                                $db = config('database.connections.ssdp.database');
                                $key = "parlimens_table:counts:duns:{$db}";
                                return Cache::store('file')->remember($key, now()->addDay(), fn () => \App\Models\Dun::count());
                            })
                    ),
                TextColumn::make('daerahs_count')
                    ->label('Bil. Daerah')
                    ->badge()
                    ->searchable(false)
                    ->summarize(
                        Summarizer::make()
                            ->label('Jumlah Daerah')
                            ->using(function ($query) {
                                $db = config('database.connections.ssdp.database');
                                $key = "parlimens_table:counts:daerahs:{$db}";
                                return Cache::store('file')->remember($key, now()->addDay(), fn () => \App\Models\Daerah::count());
                            })
                    ),
                TextColumn::make('lokalitis_count')
                    ->label('Bil. Lokaliti')
                    ->badge()
                    ->searchable(false)
                    ->summarize(
                        Summarizer::make()
                            ->label('Jumlah Lokaliti')
                            ->using(function ($query) {
                                $db = config('database.connections.ssdp.database');
                                $key = "parlimens_table:counts:lokalitis:{$db}";
                                return Cache::store('file')->remember($key, now()->addDay(), fn () => \App\Models\Lokaliti::count());
                            })
                    ),
                TextColumn::make('pengundis_count')
                    ->label('Bil. Pengundi')
                    ->badge()
                    ->searchable(false)
                    ->state(fn ($record) => PengundiCountService::getCount($record->Kod_Negeri, $record->Kod_Parlimen))
                    ->summarize(
                        Summarizer::make()
                            ->label('Jumlah Pengundi')
                            ->using(function ($query) {
                                $db = config('database.connections.ssdp.database');
                                $key = "parlimens_table:counts:pengundis:{$db}";
                                return Cache::store('file')->remember($key, now()->addDay(), fn () => \App\Models\Pengundi::count());
                            })
                    ),
            ])
            ->filters([
                //
            ])
            // Use default pagination to reduce per-request work
            ;
    }
}
