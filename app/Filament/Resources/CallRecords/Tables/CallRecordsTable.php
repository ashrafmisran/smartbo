<?php

namespace App\Filament\Resources\CallRecords\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Models\CallRecord;

class CallRecordsTable
{
    protected static array $pengundiDetailsCache = [];

    public static function configure(Table $table): Table
    {
        return $table
            ->query(static::getFilteredQuery())
            ->columns([
                Split::make([

                    TextColumn::make('user.name')
                        ->label('User')
                        ->sortable()
                        ->searchable()
                        ->hidden(!auth()->user()?->is_admin)
                        ->toggleable(isToggledHiddenByDefault: auth()->user()?->is_admin),
                        
                    TextColumn::make('pengundi_ic')
                        ->label('IC Pengundi')
                        ->searchable()
                        ->sortable(),
                        
                    TextColumn::make('phone_number')
                        ->label('Nombor Telefon')
                        ->url(fn ($record) => $record->phone_number ? "tel:" . preg_replace('/\D/', '', $record->phone_number) : null)
                        ->searchable()
                        ->badge()
                        ->sortable(),                    
                    
                    TextColumn::make('nama_pengundi')
                        ->label('Nama')
                        ->getStateUsing(fn ($record) => $record->pengundi?->Nama ?? ''),

                    TextColumn::make('nama_dun')
                        ->label('Nama DUN')
                        ->getStateUsing(fn ($record) => static::getPengundiDetails($record->pengundi_ic)['Nama_DUN'] ?? ''),

                    TextColumn::make('nama_daerah')
                        ->label('Nama Daerah')
                        ->getStateUsing(fn ($record) => static::getPengundiDetails($record->pengundi_ic)['Nama_Daerah'] ?? ''),
                    
                    TextColumn::make('kod_cula')
                        ->label('Kod Cula')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'VA' => 'gray',
                            'VB' => 'success',
                            'VC' => 'warning', 
                            'VD' => 'primary',
                            'VN' => 'info',
                            'VS' => 'secondary',
                            'VT' => 'secondary',
                            'VR' => 'danger',
                            'VW' => 'danger',
                            'VX' => 'warning',
                            'VY' => 'danger',
                            'VZ' => 'gray',
                            default => 'gray',
                        })
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'VA' => '🤷🏻‍♂️ Atas Pagar',
                            'VB' => '💚 Undi Bulan',
                            'VC' => '⚪ Condong Bulan',
                            'VD' => '⚖️ BN',
                            'VN' => '🚀 PH',
                            'VS' => '🪢 PN',
                            'VT' => '🪢 Rakan PN',
                            'VR' => '🌸 Bersatu',
                            'VW' => '❌ Salah nombor',
                            'VX' => '📵 Tiada jawapan',
                            'VY' => '🙅🏻‍♂️ Enggan respon',
                            'VZ' => '💆🏻‍♂️ Benci politik',
                            default => $state,
                        })
                        ->searchable(),
                        
                    TextColumn::make('notes')
                        ->label('Catatan')
                        ->limit(50)
                        ->tooltip(function (TextColumn $column): ?string {
                            $state = $column->getState();
                            if (strlen($state) <= 50) {
                                return null;
                            }
                            return $state;
                        })
                        ->searchable(),
                        
                    TextColumn::make('called_at')
                        ->label('Masa Panggilan')
                        ->dateTime('d/m/Y H:i')
                        ->sortable()
                        ->since()
                        ->default(fn ($record) => $record->created_at),
                        
                ])
                ->from('md')
            ])
            ->filters([
                SelectFilter::make('kod_cula')
                    ->label('Kod Cula')
                    ->options([
                        'VA' => '🤷🏻‍♂️ Atas Pagar',
                        'VB' => '💚 Undi Bulan',
                        'VC' => '⚪ Condong Bulan',
                        'VD' => '⚖️ BN',
                        'VN' => '🚀 PH',
                        'VS' => '🪢 PN',
                        'VT' => '🪢 Rakan PN',
                        'VR' => '🌸 Bersatu',
                        'VW' => '❌ Salah nombor',
                        'VX' => '📵 Tiada jawapan',
                        'VY' => '🙅🏻‍♂️ Enggan respon',
                        'VZ' => '💆🏻‍♂️ Benci politik',
                    ]),
                    
                Filter::make('called_at')
                    ->form([
                        DatePicker::make('called_from')
                            ->label('Dipanggil dari'),
                        DatePicker::make('called_until')
                            ->label('Dipanggil hingga'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['called_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('called_at', '>=', $date),
                            )
                            ->when(
                                $data['called_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('called_at', '<=', $date),
                            );
                    }),

                // Show user filter only for admin users    
                SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->visible(fn (): bool => auth()->user()?->is_admin ?? false),
            ])
            ->defaultSort('called_at', 'desc')
            // ->recordActions([
            //     ViewAction::make(),
            //     EditAction::make()
            //         ->visible(fn ($record): bool => 
            //             auth()->user()?->is_admin || 
            //             $record->user_id === auth()->id()
            //         ),
            // ])
            ->toolbarActions([
                Action::make('download')
                    ->label('Muat Turun CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function () {
                        $csvContent = static::generateCsvContent();
                        $filename = 'rekod-panggilan-' . now()->format('Y-m-d-His') . '.csv';
                        
                        return response()->streamDownload(function () use ($csvContent) {
                            echo $csvContent;
                        }, $filename, [
                            'Content-Type' => 'text/csv',
                            'Content-Disposition' => 'attachment',
                        ]);
                    })
                    ->authorize(fn (): bool => auth()->user()->status !== 'pending'),
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }

    protected static function getFilteredQuery(): Builder
    {
        $query = CallRecord::query()
            ->select([
                'id',
                'user_id',
                'pengundi_ic',
                'phone_number',
                'kod_cula',
                'notes',
                'called_at',
                'created_at',
            ])
            ->with('user');
        
        // Apply state-based access control
        if (!auth()->user()?->is_admin) {
            // Regular users - only their own records
            $query->where('user_id', auth()->id());
        } elseif (!auth()->user()?->is_superadmin) {
            // Regular admins - only users from same state
            $currentUserState = auth()->user()?->divisionKawasan?->negeri;
            
            if ($currentUserState) {
                $query->whereHas('user.divisionKawasan', function ($q) use ($currentUserState) {
                    $q->where('negeri', $currentUserState);
                });
            } else {
                // If admin has no state, show no records
                $query->whereRaw('1 = 0');
            }
        }
        // Superadmins see all records (no additional filtering)
        
        return $query;
    }

    protected static function generateCsvContent(): string
    {
        // Use same filtered query as table
        $records = static::getFilteredQuery()
            ->orderBy('called_at', 'desc')
            ->get();
        
        // Create CSV content
        $csvData = [];
        
        // Headers in Malay
        $headers = [
            'Nama User',
            'IC Pengundi',
            'Nombor Telefon',
            'Nama',
            'Nama DUN',
            'Nama Daerah',
            'Kod Cula',
            'Makna Cula',
            'Catatan',
            'Masa Panggilan',
            'Tarikh Rekod'
        ];
        
        $csvData[] = $headers;
        
        // Add data rows
        $pengundiIcs = $records->pluck('pengundi_ic')
            ->filter()
            ->unique()
            ->values()
            ->all();

        static::loadPengundiDetails($pengundiIcs);

        foreach ($records as $record) {
            $pengundi = static::getPengundiDetails($record->pengundi_ic);

            $csvData[] = [
                $record->user?->name ?? '',
                $record->pengundi_ic ?? '',
                $record->phone_number ?? '',
                $pengundi['Nama'] ?? '',
                $pengundi['Nama_DUN'] ?? '',
                $pengundi['Nama_Daerah'] ?? '',
                $record->kod_cula ?? '',
                static::getCulaMeaning($record->kod_cula),
                $record->notes ?? '',
                $record->called_at?->format('d/m/Y H:i') ?? '',
                $record->created_at?->format('d/m/Y H:i') ?? '',
            ];
        }
        
        // Generate CSV string
        $csvContent = '';
        foreach ($csvData as $row) {
            $csvContent .= '"' . implode('","', str_replace('"', '""', $row)) . '"' . "\r\n";
        }
        
        return $csvContent;
    }

    protected static function loadPengundiDetails(array $pengundiIcs): void
    {
        $missingIcs = array_diff($pengundiIcs, array_keys(static::$pengundiDetailsCache));

        if (empty($missingIcs)) {
            return;
        }

        $rows = DB::connection('ssdp')
            ->table('daftara')
            ->whereIn('No_KP_Baru', $missingIcs)
            ->join('dun', 'daftara.Kod_DUN', '=', 'dun.Kod_DUN')
            ->join('daerah', function ($join) {
                $join->on('daftara.Kod_Daerah', '=', 'daerah.Kod_Daerah')
                     ->on('daftara.Kod_DUN', '=', 'daerah.Kod_DUN');
            })
            ->get([
                'daftara.No_KP_Baru as No_KP_Baru',
                'daftara.Nama as Nama',
                'dun.Nama_DUN as Nama_DUN',
                'daerah.Nama_Daerah as Nama_Daerah',
            ]);

        foreach ($rows as $pengundi) {
            static::$pengundiDetailsCache[$pengundi->No_KP_Baru] = [
                'Nama' => $pengundi->Nama,
                'Nama_DUN' => $pengundi->Nama_DUN,
                'Nama_Daerah' => $pengundi->Nama_Daerah,
            ];
        }

        foreach ($missingIcs as $ic) {
            if (! array_key_exists($ic, static::$pengundiDetailsCache)) {
                static::$pengundiDetailsCache[$ic] = [
                    'Nama' => '',
                    'Nama_DUN' => '',
                    'Nama_Daerah' => '',
                ];
            }
        }
    }

    protected static function getPengundiDetails(?string $pengundiIc): array
    {
        if (! $pengundiIc) {
            return [
                'Nama' => '',
                'Nama_DUN' => '',
                'Nama_Daerah' => '',
            ];
        }

        if (! array_key_exists($pengundiIc, static::$pengundiDetailsCache)) {
            static::loadPengundiDetails([$pengundiIc]);
        }

        return static::$pengundiDetailsCache[$pengundiIc];
    }

    protected static function getCulaMeaning($kodCula): string
    {
        return match ($kodCula) {
            'VA' => 'Atas Pagar',
            'VB' => 'Undi Bulan',
            'VC' => 'Condong Bulan',
            'VD' => 'BN',
            'VN' => 'PH',
            'VS' => 'PN',
            'VT' => 'Rakan PN',
            'VR' => 'Bersatu',
            'VW' => 'Salah nombor',
            'VX' => 'Tiada jawapan',
            'VY' => 'Enggan respon',
            'VZ' => 'Benci politik',
            default => $kodCula ?? '',
        };
    }
}
