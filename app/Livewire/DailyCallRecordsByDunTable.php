<?php

namespace App\Livewire;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Models\CallRecord;
use App\Models\Dun;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DailyCallRecordsByDunTable extends TableWidget
{
    protected static ?string $heading = 'Bilangan pengundi dihubungi setiap hari mengikut DUN';
    
    protected int|string|array $columnSpan = 'full';
    
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        // Get all DUN names for dynamic columns
        $duns = $this->getDunList();
        
        $columns = [
            TextColumn::make('date')
                ->label('Tarikh')
                ->date('d/m/Y')
                ->sortable()
                ->extraAttributes(['class' => 'sticky left-0 bg-white z-10 border-r']),
        ];

        // Add a column for each DUN
        foreach ($duns as $dunCode => $dunName) {
            $columns[] = TextColumn::make("dun_{$dunCode}")
                ->label($dunName)
                ->numeric()
                ->alignCenter()
                ->formatStateUsing(fn ($state) => $state ?? 0);
        }

        // Add total column
        $columns[] = TextColumn::make('total')
            ->label('Jumlah')
            ->numeric()
            ->alignCenter()
            ->weight('bold')
            ->color('primary');

        return $table
            ->records(fn () => $this->getTableData())
            ->columns($columns)
            ->paginated([15, 30, 50])
            ->poll('30s')
            ->contentGrid([
                'md' => 1,
                'xl' => 1,
            ])
            ->extraAttributes(['class' => 'overflow-x-auto']);
    }

    protected function getTableData()
    {
        $duns = $this->getDunList();
        
        // Get call records data grouped by date and DUN
        $callData = $this->getCallRecordsData();
        
        // Generate date range (from 15/11/2025 onwards - latest first)
        $dates = [];
        $startDate = Carbon::parse('2025-11-15');
        $endDate = Carbon::now();
        
        // Calculate days from start date to today
        $daysDiff = $startDate->diffInDays($endDate);
        
        for ($i = 0; $i <= $daysDiff; $i++) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            
            // Only include dates from 15/11/2025 onwards
            if ($date >= '2025-11-15') {
            
            $row = [
                'date' => $date,
                'total' => 0
            ];
            
            // Initialize all DUN columns with 0
            foreach ($duns as $dunCode => $dunName) {
                $row["dun_{$dunCode}"] = 0;
            }
            
            // Fill with actual data if exists
            if (isset($callData[$date])) {
                foreach ($callData[$date] as $dunCode => $count) {
                    if (isset($duns[$dunCode])) {
                        $row["dun_{$dunCode}"] = $count;
                        $row['total'] += $count;
                    }
                }
            }
            
            $dates[] = $row;
            }
        }
        
        // Return collection directly
        return collect($dates);
    }

    protected function getCallRecordsData(): array
    {
        // Get call records first (from 15/11/2025 onwards)
        $callRecords = CallRecord::query()
            ->whereNotNull('kod_cula')
            ->where('created_at', '>=', '2025-11-15')
            ->select(['pengundi_ic', DB::raw('DATE(created_at) as date')])
            ->get()
            ->groupBy('date');

        // Get pengundi to DUN mapping from ssdp database
        $pengundi_ics = CallRecord::query()
            ->whereNotNull('kod_cula')
            ->where('created_at', '>=', '2025-11-15')
            ->distinct()
            ->pluck('pengundi_ic')
            ->toArray();

        $pengundiToDun = DB::connection('ssdp')
            ->table('daftara')
            ->whereIn('No_KP_Baru', $pengundi_ics)
            ->select(['No_KP_Baru', 'Kod_DUN'])
            ->pluck('Kod_DUN', 'No_KP_Baru')
            ->toArray();

        // Process the data
        $groupedData = [];
        foreach ($callRecords as $date => $records) {
            $dunCounts = [];
            foreach ($records as $record) {
                $kodDun = $pengundiToDun[$record->pengundi_ic] ?? null;
                if ($kodDun) {
                    $dunCounts[$kodDun] = ($dunCounts[$kodDun] ?? 0) + 1;
                }
            }
            if (!empty($dunCounts)) {
                $groupedData[$date] = $dunCounts;
            }
        }

        return $groupedData;
    }

    protected function getDunList(): array
    {
        $dunQuery = Dun::query();
        
        if (!auth()->user()?->is_superadmin) {
            $currentUserState = auth()->user()?->divisionKawasan?->negeri;
            
            if ($currentUserState) {
                // Map state names to state codes for DUN filtering
                $stateCodeMap = [
                    'Johor' => '01',
                    'Kedah' => '02',
                    'Kelantan' => '03',
                    'Melaka' => '04',
                    'Negeri Sembilan' => '05',
                    'Pahang' => '06',
                    'Perak' => '08',
                    'Perlis' => '09',
                    'Pulau Pinang' => '07',
                    'Sabah' => '12',
                    'Sarawak' => '13',
                    'Selangor' => '10',
                    'Terengganu' => '11',
                    'Wilayah Persekutuan' => '14'
                ];
                
                $stateCode = $stateCodeMap[$currentUserState] ?? null;
                
                if ($stateCode) {
                    $dunQuery->where('Kod_Negeri', $stateCode);
                } else {
                    return [];
                }
            } else {
                return [];
            }
        }

        return $dunQuery
            ->orderBy('Kod_DUN')
            ->pluck('Nama_DUN', 'Kod_DUN')
            ->toArray();
    }

    public static function canView(): bool
    {
        return auth()->user()?->is_admin || auth()->user()?->is_superadmin;
    }
}