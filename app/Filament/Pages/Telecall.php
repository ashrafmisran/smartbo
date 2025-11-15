<?php

namespace App\Filament\Pages;

use App\Models\Dun;
use App\Models\Daerah;
use App\Models\Lokaliti;
use App\Models\Pengundi;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\ToggleButtons;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Actions\Action as PageAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\Layout\Split;
use BackedEnum;
use Filament\Support\Enums\Width;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\Facades\Cache;

class Telecall extends Page implements
    Forms\Contracts\HasForms,
    Tables\Contracts\HasTable
{
    use Forms\Concerns\InteractsWithForms;
    use Tables\Concerns\InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Telecall';
    protected static ?string $title = 'Telecall';
    
    protected string $view = 'filament.pages.random-pengundi';

    // Properties to bind selected values
    public ?string $dun_id = null;
    public ?string $daerah_id = null;
    public ?string $lokaliti_id = null;
    public ?string $kategori_cula = null;
    
    // Flag to control when to show results
    public bool $showResults = false;

    // Cache the 5 random IDs to avoid re-running heavy queries on every update
    public array $randomIds = [];

    public static function getNavigationGroup(): ?string
    {
        return 'OPERASI';
    }

    public static function canAccess(): bool
    {
        return auth()->user()->status <> 'pending';
    }

    // Helper methods to pad values with leading zeros
    private function padDunCode($code): string
    {
        return str_pad($code, 2, '0', STR_PAD_LEFT);
    }

    private function padDaerahCode($code): string
    {
        return str_pad($code, 2, '0', STR_PAD_LEFT);
    }

    private function padLokalitiCode($code): string
    {
        return str_pad($code, 3, '0', STR_PAD_LEFT);
    }

    // Build the filtered base query (used for picking random IDs)
    private function buildFilteredPengundiQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = Pengundi::query();

        if (!$this->dun_id) {
            return $query->whereRaw('1 = 0');
        }

        $query->where('Kod_DUN', $this->padDunCode($this->dun_id));

        if ($this->daerah_id) {
            $query->where('Kod_Daerah', $this->padDaerahCode($this->daerah_id));
        }

        if ($this->lokaliti_id) {
            $query->where('Kod_Lokaliti', $this->padLokalitiCode($this->lokaliti_id));
        }

        // Filter by kategori cula
        if($this->kategori_cula){

            $query->where(function ($q) {
                $q->whereNull('Kod_Cula')
                    ->orWhere('Kod_Cula', '');
            });

            // Additional filtering by bangsa within belum cula
            if ($this->kategori_cula === 'Belum Cula Melayu') {
                $query->where('Keturunan','M');
            }
            
            if ($this->kategori_cula === 'Belum Cula Bukan Melayu') {
                $query->where('Keturunan', '!=','M');
            }
        }

        // Only include records that have at least one phone number
        return $query ;//->whereHas('Tel_Bimbit')->orWhereHas('Tel_Rumah');
    }

    // Form schema for filters
    protected function getFormSchema(): array
    {
        $dunOptions = Cache::remember('dun_options', 3600, fn() => Dun::pluck('Nama_DUN', 'Kod_DUN')->toArray());
        
        return [
            Section::make()
                ->schema([
                    Grid::make()
                        ->schema([
                            Select::make('kategori_cula')
                                ->label('Kategori Cula')
                                ->options([
                                    'SEMUA'=>'Semua',
                                    'Belum Cula Melayu'=>'Belum Cula Melayu',
                                    'Belum Cula Bukan Melayu'=>'Belum Cula Bukan Melayu'
                                ])
                                ->live()
                                ->required()
                                ->autoFocus()
                                ->afterStateUpdated(function () {
                                    $this->showResults = false;
                                    $this->randomIds = [];
                                })
                                ->placeholder('Pilih Kategori Cula')
                                ->searchable()
                                ->preload()
                                ->autoFocus()
                                ->columnSpan(3),

                            Select::make('dun_id')
                                ->label('DUN')
                                ->options($dunOptions)
                                ->placeholder('Pilih DUN')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('daerah_id', null);
                                    $set('lokaliti_id', null);
                                    $this->showResults = false;
                                    $this->randomIds = [];
                                })
                                ->columnSpan(3),

                            Select::make('daerah_id')
                                ->label('Daerah')
                                ->options(function (callable $get) {
                                    $dunId = $get('dun_id');
                                    if (!$dunId) {
                                        return [];
                                    }
                                    $cacheKey = "daerah:{$dunId}";
                                    return Cache::remember($cacheKey, 3600, function () use ($dunId) {
                                        return Daerah::where('Kod_DUN', $dunId)
                                            ->orderBy('Kod_Daerah')
                                            ->get()
                                            ->mapWithKeys(fn ($daerah) => [
                                                $daerah->Kod_Daerah => "{$daerah->Kod_Daerah} - {$daerah->Nama_Daerah}",
                                            ])
                                            ->toArray();
                                    });
                                })
                                ->placeholder('Pilih Daerah (Opsional)')
                                ->searchable()
                                ->visible(fn (callable $get) => !empty($get('dun_id')))
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('lokaliti_id', null);
                                    $this->showResults = false;
                                    $this->randomIds = [];
                                })
                                ->preload()
                                ->columnSpan(3),

                            Select::make('lokaliti_id')
                                ->label('Lokaliti')
                                ->options(function (callable $get) {
                                    $dunId = $get('dun_id');
                                    $daerahId = $get('daerah_id');
                                    
                                    if (!$dunId) {
                                        return [];
                                    }
                                    $key = "lokaliti:{$dunId}:" . ($daerahId ?: 'all');
                                    return Cache::remember($key, 3600, function () use ($dunId, $daerahId) {
                                        $query = Lokaliti::where('Kod_DUN', $dunId);
                                        if ($daerahId) {
                                            $query->where('Kod_Daerah', $daerahId);
                                        }
                                        return $query->orderBy('Kod_Lokaliti')
                                            ->get()
                                            ->mapWithKeys(fn ($lokaliti) => [
                                                $lokaliti->Kod_Lokaliti => "{$lokaliti->Kod_Lokaliti} - {$lokaliti->Nama_Lokaliti}",
                                            ])
                                            ->toArray();
                                    });
                                })
                                ->placeholder('Pilih Lokaliti (Opsional)')
                                ->searchable()
                                ->visible(fn (callable $get) => !empty($get('dun_id')) && !empty($get('daerah_id')))
                                ->live()
                                ->afterStateUpdated(function () {
                                    $this->showResults = false;
                                    $this->randomIds = [];
                                })
                                ->columnSpan(3),

                            Grid::make()
                                ->schema([
                                    PageAction::make('generate')
                                        ->icon('heroicon-o-arrow-right-circle')
                                        ->color('success')
                                        ->label('Jana')
                                        ->size('lg')
                                        ->visible(fn () => $this->dun_id !== null)
                                        ->action(function () {
                                            // Pick 5 random IDs once
                                            $this->randomIds = $this->buildFilteredPengundiQuery()
                                                ->inRandomOrder()
                                                ->limit(1)
                                                ->pluck('No_KP_Baru')
                                                ->all();

                                            $this->showResults = true;
                                            $this->resetTable();
                                        }),
                                ])
                                ->columnSpanFull()
                        ])
                        ->columns(12)

                ]),
        ];
    }

    // Table query
    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // Return empty unless we have preselected IDs
        if (!$this->showResults || empty($this->randomIds)) {
            return Pengundi::query()->whereRaw('1 = 0');
        }

        // Cheap whereIn query for snappy re-renders
        return Pengundi::query()
            ->whereIn('No_KP_Baru', $this->randomIds)
            ->whereHas('bancian', function ($query) {
                $query->whereNotNull('Tel_Bimbit')
                      ->where('Tel_Bimbit', '!=', '');
            })
            ->with('negeri', 'parlimen', 'dun'); // todo: to add phone_numbers once relationship issue resolved
    }

    // Disable pagination
    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }

    // Table columns
    protected function getTableColumns(): array
    {
        return [
            Split::make([

                TextColumn::make('No_KP_Baru')
                    ->label('No. KP')
                    ->description(
                        function ($record) {
                            $umur = now()->year - (2000 + (int)substr($record->No_KP_Baru, 0, 2));
                            if ($umur < 18) $umur += 100;
                            $jantina = ((int)substr($record->No_KP_Baru, -2) % 2 === 0) ? 'Perempuan' : 'Lelaki';
                            return "Umur: {$umur} tahun | Jantina: {$jantina}";
                        }
                    )
                    ->width('150px'),
    
                TextColumn::make('Nama')
                    ->label('Nama Pengundi')
                    ->weight('bold')
                    ->description(
                        fn ($record) => 'Agama: ' . ($record->Agama ?? '-') . ' | Bangsa: ' . ($record->Keturunan ?? '-') . ' | Etnik: ' . ($record->Bangsa ?? '-')
                    ),

            ])->from('md')

        ];
    }

    protected function getTableActions(): array
    {
        return [
            Action::make('hubungi')
                ->icon('heroicon-o-phone')
                ->iconButton()
                ->color('success')
                ->url(fn (Pengundi $record) => "/bo/senarai-pengundi/{$record->No_KP_Baru}/telecall")
                ->openUrlInNewTab(),
        ];
    }

    // Table empty state
    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-information-circle';
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'Sila pilih DUN untuk mula';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Klik "Jana" semula untuk dapatkan pengundi rawak.';
    }

    // Mount method to initialize form
    public function mount(): void
    {
        $this->kategori_cula = 'SEMUA';
        $this->form->fill([
            'kategori_cula' => 'SEMUA'
        ]);
    }
    // Page header actions
    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}