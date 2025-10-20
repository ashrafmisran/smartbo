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
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use BackedEnum;

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

    // Form schema for filters
    protected function getFormSchema(): array
    {
        return [
            Section::make()
                ->schema([
                    Grid::make()
                        ->schema([
                            Select::make('dun_id')
                                ->label('DUN')
                                ->options(fn () => Dun::query()
                                    ->orderBy('Kod_DUN')
                                    ->get()
                                    ->mapWithKeys(fn ($dun) => [$dun->Kod_DUN => "{$dun->Kod_DUN} - {$dun->Nama_DUN}"])
                                    ->toArray())
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
                                })
                                ->columnSpan(3),

                            Select::make('daerah_id')
                                ->label('Daerah')
                                ->options(function (callable $get) {
                                    $dunId = $get('dun_id');
                                    if (!$dunId) {
                                        return [];
                                    }
                                    return Daerah::where('Kod_DUN', $dunId)
                                        ->orderBy('Kod_Daerah')
                                        ->get()
                                        ->mapWithKeys(fn ($daerah) => [$daerah->Kod_Daerah => "{$daerah->Kod_Daerah} - {$daerah->Nama_Daerah}"])
                                        ->toArray();
                                })
                                ->placeholder('Pilih Daerah (Opsional)')
                                ->searchable()
                                ->visible(fn (callable $get) => !empty($get('dun_id')))
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('lokaliti_id', null);
                                    $this->showResults = false;
                                })
                                ->columnSpan(3),

                            Select::make('lokaliti_id')
                                ->label('Lokaliti')
                                ->options(function (callable $get) {
                                    $dunId = $get('dun_id');
                                    $daerahId = $get('daerah_id');
                                    
                                    if (!$dunId) {
                                        return [];
                                    }
                                    
                                    $query = Lokaliti::where('Kod_DUN', $dunId);
                                    
                                    if ($daerahId) {
                                        $query->where('Kod_Daerah', $daerahId);
                                    }
                                    
                                    return $query->orderBy('Kod_Lokaliti')
                                        ->get()
                                        ->mapWithKeys(fn ($lokaliti) => [$lokaliti->Kod_Lokaliti => "{$lokaliti->Kod_Lokaliti} - {$lokaliti->Nama_Lokaliti}"])
                                        ->toArray();
                                })
                                ->placeholder('Pilih Lokaliti (Opsional)')
                                ->searchable()
                                ->visible(fn (callable $get) => !empty($get('dun_id')) && !empty($get('daerah_id')))
                                ->live()
                                ->afterStateUpdated(function () {
                                    $this->showResults = false;
                                })
                                ->columnSpan(3),

                            Grid::make()
                                ->schema([
                                    Action::make('generate')
                                        ->icon('heroicon-o-arrow-right-circle')
                                        ->color('success')
                                        ->iconButton()
                                        ->label('Jana')
                                        ->size('lg')
                                        ->visible(fn () => $this->dun_id !== null)
                                        ->action(function () {
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
        $query = Pengundi::query(); // Now automatically selects only allowed columns
        
        // Only show results if generate button was clicked
        if (!$this->showResults || !$this->dun_id) {
            return $query->whereRaw('1 = 0'); // Return empty result
        }
        
        // Filter by DUN (required) - pad with leading zeros
        $paddedDunId = $this->padDunCode($this->dun_id);
        $query->where('Kod_DUN', $paddedDunId);
        
        // Filter by Daerah if selected - pad with leading zeros
        if ($this->daerah_id) {
            $paddedDaerahId = $this->padDaerahCode($this->daerah_id);
            $query->where('Kod_Daerah', $paddedDaerahId);
        }
        
        // Filter by Lokaliti if selected - pad with leading zeros
        if ($this->lokaliti_id) {
            $paddedLokalitiId = $this->padLokalitiCode($this->lokaliti_id);
            $query->where('Kod_Lokaliti', $paddedLokalitiId);
        }
        
        // Debug logging to verify the padded values
        \Log::info('RandomPengundi Query Debug', [
            'original_dun_id' => $this->dun_id,
            'padded_dun_id' => $paddedDunId,
            'original_daerah_id' => $this->daerah_id,
            'padded_daerah_id' => $this->daerah_id ? $this->padDaerahCode($this->daerah_id) : null,
            'original_lokaliti_id' => $this->lokaliti_id,
            'padded_lokaliti_id' => $this->lokaliti_id ? $this->padLokalitiCode($this->lokaliti_id) : null,
        ]);
        
        $result = $query->inRandomOrder()->limit(5);
        
        // Debug: Log the final count and query
        \Log::info('Final Query Count: ' . $result->count());
        \Log::info('SQL Query: ' . $result->toSql());
        \Log::info('Query Bindings: ', $result->getBindings());
        
        return $result;
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

            TextColumn::make('Kod_DUN')
                ->label('DUN')
                ->badge()
                ->color('success')
                ->formatStateUsing(fn ($state) => $state ? "DUN-{$state}" : '--')
                ->default('--'),

            TextColumn::make('Kod_Daerah')
                ->label('Daerah')
                ->badge()
                ->color('warning')
                ->formatStateUsing(fn ($state) => $state ? "Daerah-{$state}" : '--')
                ->default('--'),

            TextColumn::make('Kod_Lokaliti')
                ->label('Lokaliti')
                ->badge()
                ->color('info')
                ->formatStateUsing(fn ($state) => $state ? "Lokaliti-{$state}" : '--')
                ->default('--'),

            TextColumn::make('Bangsa')
                ->label('Bangsa')
                ->badge()
                ->color('gray')
                ->default('--'),

            TextColumn::make('Agama')
                ->label('Agama')
                ->badge()
                ->color('primary')
                ->default('--'),
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
            Action::make('regenerate')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->label('Jana Semula')
                ->visible(fn () => $this->showResults && $this->dun_id)
                ->action(function () {
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