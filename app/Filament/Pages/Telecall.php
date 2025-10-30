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
    
    // Flag to control when to show results
    public bool $showResults = false;

    // Cache the 5 random IDs to avoid re-running heavy queries on every update
    public array $randomIds = [];

    public static function getNavigationGroup(): ?string
    {
        return 'OPERASI';
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

        // Only include records that have at least one phone number
        return $query->whereHas('tel_numbers');
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
                            Select::make('dun_id')
                                ->label('DUN')
                                ->options($dunOptions)
                                ->placeholder('Pilih DUN')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->autoFocus()
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
                                        ->iconButton()
                                        ->label('Jana')
                                        ->size('lg')
                                        ->visible(fn () => $this->dun_id !== null)
                                        ->action(function () {
                                            // Pick 5 random IDs once
                                            $this->randomIds = $this->buildFilteredPengundiQuery()
                                                ->inRandomOrder()
                                                ->limit(5)
                                                ->pluck('No_KP_Baru')
                                                ->all();

                                            $this->showResults = true;
                                            $this->resetTable();

                                            Notification::make()
                                                ->success()
                                                ->title('Pengundi rawak dijana')
                                                ->body('Menunjukkan 5 pengundi rawak berdasarkan filter yang dipilih')
                                                ->send();
                                        }),
                                ])
                                ->columnSpan(1)
                        ])
                        ->columns(10)

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
            ->with('negeri', 'parlimen', 'dun','tel_numbers');
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
            TextColumn::make('No_KP_Baru')
                ->label('No. KP')
                ->searchable()
                ->sortable()
                ->copyable()
                ->copyMessage('No. KP disalin')
                ->width('150px'),

            TextColumn::make('Nama')
                ->label('Nama Pengundi')
                ->searchable()
                ->sortable()
                ->weight('bold')
                ->description(fn ($record) => $record->Keturunan ? "Keturunan: {$record->Keturunan}" : null),

        ];
    }

    protected function getTableActions(): array
    {
        return [
            Action::make('call')
                ->icon('heroicon-o-phone')
                ->iconButton()
                ->schema([
                    Grid::make()
                        ->schema([
                            Section::make('Maklumat Pengundi')
                                ->schema([
                                    TextEntry::make('Nama')
                                        ->label('Nama Pengundi')
                                        ->default(fn (Pengundi $record) => $record->Nama),
                                ])
                                ->columnSpan(4),
                            Section::make('')
                                ->extraAttributes([
                                    'class' => 'max-h-4 overflow-y-auto p-4 border rounded-lg',
                                ])
                                ->schema([
                                    TextEntry::make('skrip')
                                        ->label('Skrip Panggilan')
                                        ->html()  // TODO: to make this scrollable if too long
                                        ->default('
                                            <h2>Skrip Panggilan Telefon – Perikatan Nasional</h2>

                                            <p class="text-blue-500"><strong>Pembuka:</strong><br>
                                            Assalamualaikum, saya [NAMA PEMANGGIL] dari pasukan sukarelawan Perikatan Nasional. 
                                            Maaf ganggu sekejap, boleh saya ambil sedikit masa Tuan/Puan?</p>
                                            <hr>
                                            <h2><strong>Bahagian 1: Soalan Kajian Halus</strong></h2>
                                            <p>
                                            Kami sedang buat sedikit tinjauan ringan — pada pandangan Tuan/Puan, 
                                            kalau ada pilihan, siapakah yang Tuan/Puan rasa paling layak menjaga kebajikan rakyat di 
                                            DUN [NAMA DUN] nanti?<br>
                                            (Dengar jawapan dengan sopan. Jika sebut parti lain, jangan lawan; 
                                            ucap terima kasih dan teruskan bahagian seterusnya dengan nada berhemah.)</p>
                                            <hr>
                                            <h2><strong>Bahagian 2: Jambatan ke Mesej Kempen</strong></h2>
                                            <p>Terima kasih atas pandangan Tuan/Puan.</p>
                                            <p>Kami juga ingin kongsikan sedikit — Perikatan Nasional kini berusaha membawa politik 
                                            yang lebih bersih dan berprinsip.</p>
                                            <p>Alhamdulillah, semasa waktu sukar dulu seperti Covid-19, kerajaan di bawah pimpinan PN 
                                            telah menjaga kebajikan rakyat, bantu dari segi bantuan tunai, moratorium, 
                                            dan inisiatif ekonomi rakyat.</p>
                                            <p>Ramai rakyat waktu itu rasa lega kerana keprihatinan dan kecekapan pentadbiran PN.</p>
                                            <hr>
                                            <h2><strong>Bahagian 3: Penutup Kempen</strong></h2>
                                            <p>Sebab itu, kami mengajak Tuan/Puan supaya pertimbangkan untuk 
                                            mengundi calon Perikatan Nasional di DUN [NAMA DUN].</p>
                                            <p>Kami percaya wakil PN akan terus membawa suara rakyat dengan integriti, 
                                            amanah dan keprihatinan — bukan janji kosong, tapi sudah terbukti dengan tindakan.</p>
                                            <p>Terima kasih banyak atas masa Tuan/Puan. Semoga Allah permudahkan urusan kita semua 
                                            dan berkati pilihan yang dibuat.</p>

                                            <p><strong>Jika penerima neutral atau positif:</strong><br>
                                            Terima kasih Tuan/Puan atas sokongan. Insya-Allah, kita doakan PN terus kuat 
                                            untuk bela rakyat.</p>

                                            <p><strong>Jika penerima menolak:</strong><br>
                                            Tidak mengapa Tuan/Puan, kami hormati pandangan. Terima kasih kerana sudi luangkan masa.</p>

                                        '),
                                ])
                                ->columnSpan(6),
                            Section::make('Rekod panggilan ini')
                                ->schema([
                                    // Fix name to cula_pn and make updates immediate with minimal overhead
                                    ToggleButtons::make('cula_pn')
                                        ->label('Cula PN')
                                        ->options([
                                            'VB' => 'PAS',
                                            'VC' => 'Condong PAS',
                                            'VS' => 'PN',
                                            'VT' => 'Rakan PN',
                                        ])
                                        ->grouped()
                                        ->live(debounce: 0)
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            if ($state) {
                                                $set('cula_lawan', null);
                                                $set('cula_lain', null);
                                            }
                                        }),
                                    ToggleButtons::make('cula_lawan')
                                        ->label('Cula Lawan')
                                        ->options([
                                            'VD' => 'BN',
                                            'VN' => 'PH',
                                            'VR' => 'GRS',
                                        ])
                                        ->grouped()
                                        ->live(debounce: 0)
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            if ($state) {
                                                $set('cula_pn', null);
                                                $set('cula_lain', null);
                                            }
                                        }),
                                    ToggleButtons::make('cula_lain')
                                        ->label('Cula Lain')
                                        ->options([
                                            'VA' => 'Atas Pagar',
                                            'VW' => 'Salah nombor',
                                            'VX' => 'Tidak angkat',
                                            'VY' => 'Tidak bersedia untuk jawab',
                                            'VZ' => 'Anti politik',
                                        ])
                                        ->live(debounce: 0)
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            if ($state) {
                                                $set('cula_pn', null);
                                                $set('cula_lawan', null);
                                            }
                                        }),
                                ])
                                ->columnSpan(5)
                        ])
                        ->columns(15),
                ])
                ->modalWidth(Width::Screen)
                ->slideOver(true)
                ->color('success')
                ->extraAttributes(['class' => 'absolute']) // enable absolute overlay positioning
                ->button(),
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
        return 'Pilih DUN dan klik "Jana" untuk melihat 5 pengundi rawak.';
    }

    // Mount method to initialize form
    public function mount(): void
    {
        $this->form->fill([]);
    }

    // Page header actions
    protected function getHeaderActions(): array
    {
        return [
            PageAction::make('regenerate')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->label('Jana Semula')
                ->visible(fn () => $this->showResults && $this->dun_id)
                ->action(function () {
                    // Re-pick 5 random IDs quickly
                    $this->randomIds = $this->buildFilteredPengundiQuery()
                        ->inRandomOrder()
                        ->limit(5)
                        ->pluck('No_KP_Baru')
                        ->all();

                    $this->resetTable();
                    
                    Notification::make()
                        ->success()
                        ->title('Pengundi rawak dijana semula')
                        ->body('5 pengundi rawak baharu telah dijana')
                        ->send();
                }),
        ];
    }
}