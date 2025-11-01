<?php

namespace App\Filament\Resources\Duns\Tables;

use App\Models\Dun;
use App\Services\PengundiCountByDunService;
use Illuminate\Support\Facades\Cache;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DunsTable
{
    protected static function getTableQuery(): Builder
    {
        return Dun::query()
            ->select([
                'dun.REC_ID',
                'dun.Kod_Negeri',
                'dun.Kod_Parlimen',
                'dun.Kod_DUN',
                'dun.Nama_DUN',
            ])
            ->with([
                'negeri:Kod_Negeri,Nama_Negeri',
                'parlimen:Kod_Parlimen,Nama_Parlimen',
            ])
            ->withCount([
                'daerahs',
                'lokalitis',
            ])
            ->orderBy('dun.Nama_DUN');
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->query(self::getTableQuery())
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
                            ->using(function ($query) {
                                $db = config('database.connections.ssdp.database');
                                return Cache::store('file')->remember("duns_table:counts:daerahs:{$db}", now()->addDay(), fn () => \App\Models\Daerah::count());
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
                                return Cache::store('file')->remember("duns_table:counts:lokaliti:{$db}", now()->addDay(), fn () => \App\Models\Lokaliti::count());
                            })
                    ),
                TextColumn::make('pengundis_count')
                    ->label('Bil. Pengundi')
                    ->badge()
                    ->searchable(false)
                    ->state(fn ($record) => PengundiCountByDunService::getCount($record->Kod_Negeri, $record->Kod_Parlimen, $record->Kod_DUN))
                    ->summarize(
                        Summarizer::make()
                            ->label('Jumlah Pengundi')
                            ->using(function ($query) {
                                $db = config('database.connections.ssdp.database');
                                return Cache::store('file')->remember("duns_table:counts:pengundis:{$db}", now()->addDay(), fn () => \App\Models\Pengundi::count());
                            })
                    ),
            ])
            ->filters([
                //
            ]);
    }
}
