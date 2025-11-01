<?php

namespace App\Filament\Pages;

use App\Models\Dun;
use App\Models\Daerah;
use App\Models\Lokaliti;
use App\Models\Pengundi;
use App\Services\FamilyAnalysisService;
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

class UrusMaklumatIsiRumah extends Page implements
    Forms\Contracts\HasForms,
    Tables\Contracts\HasTable
{
    use Forms\Concerns\InteractsWithForms;
    use Tables\Concerns\InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Urus Maklumat Isi Rumah';
    protected static ?string $title = 'Urus Maklumat Isi Rumah';

    protected string $view = 'filament.pages.urus-maklumat-isi-rumah';

    // Properties to bind selected values
    public ?string $dun_id = null;
    public ?string $daerah_id = null;
    public ?string $lokaliti_id = null;
    
    // Flag to control when to show results
    public bool $showResults = false;
    public bool $showFamilyAnalysis = false;
    public array $familyGroups = [];
    public array $familySuggestions = [];
    public array $aiAnalysisResult = [];

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
            Section::make('Filter Maklumat Pengundi')
                ->description('Pilih DUN, Daerah, dan Lokaliti untuk menganalisis maklumat isi rumah')
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
                                ->live()
                                ->required()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('daerah_id', null);
                                    $set('lokaliti_id', null);
                                    $this->showResults = false;
                                    $this->showFamilyAnalysis = false;
                                })
                                ->columnSpan(4),

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
                                ->placeholder('Pilih Daerah')
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('lokaliti_id', null);
                                    $this->showResults = false;
                                    $this->showFamilyAnalysis = false;
                                })
                                ->columnSpan(3),

                            Select::make('lokaliti_id')
                                ->label('Lokaliti')
                                ->options(function (callable $get) {
                                    $dunId = $get('dun_id');
                                    $daerahId = $get('daerah_id');
                                    
                                    if (!$dunId || !$daerahId) {
                                        return [];
                                    }
                                    
                                    return Lokaliti::where('Kod_DUN', $dunId)
                                        ->where('Kod_Daerah', $daerahId)
                                        ->orderBy('Kod_Lokaliti')
                                        ->get()
                                        ->mapWithKeys(fn ($lokaliti) => [$lokaliti->Kod_Lokaliti => "{$lokaliti->Kod_Lokaliti} - {$lokaliti->Nama_Lokaliti}"])
                                        ->toArray();
                                })
                                ->placeholder('Pilih Lokaliti')
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function () {
                                    $this->showResults = false;
                                    $this->showFamilyAnalysis = false;
                                })
                                ->columnSpan(3),

                            Grid::make()
                                ->schema([
                                    Action::make('generate')
                                        ->icon('heroicon-o-arrow-right-circle')
                                        ->color('success')
                                        ->iconButton()
                                        ->label('Papar')
                                        ->size('lg')
                                        ->visible(fn (callable $get) => $get('dun_id') && $get('daerah_id') && $get('lokaliti_id'))
                                        ->action(function (callable $get) {
                                            $this->dun_id = $get('dun_id');
                                            $this->daerah_id = $get('daerah_id');
                                            $this->lokaliti_id = $get('lokaliti_id');
                                            $this->showResults = true;
                                            $this->showFamilyAnalysis = false;
                                            
                                            // Reset analysis data
                                            $this->familyGroups = [];
                                            $this->familySuggestions = [];
                                            $this->aiAnalysisResult = [];
                                            
                                            $this->resetTable();
                                            
                                            Notification::make()
                                                ->success()
                                                ->title('Maklumat Isi Rumah Dipaparkan')
                                                ->body('Menunjukkan pengundi berdasarkan DUN, Daerah, dan Lokaliti yang dipilih')
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
        $query = Pengundi::query();
        
        // Only show results if all required filters are selected
        if (!$this->showResults || !$this->dun_id || !$this->daerah_id || !$this->lokaliti_id) {
            return $query->whereRaw('1 = 0'); // Return empty result
        }
        
        // Filter by DUN, Daerah, and Lokaliti - pad with leading zeros
        $paddedDunId = $this->padDunCode($this->dun_id);
        $paddedDaerahId = $this->padDaerahCode($this->daerah_id);
        $paddedLokalitiId = $this->padLokalitiCode($this->lokaliti_id);
        
        $query->where('Kod_DUN', $paddedDunId)
              ->where('Kod_Daerah', $paddedDaerahId)
              ->where('Kod_Lokaliti', $paddedLokalitiId);
        
        return $query->orderBy('Nama');
    }

    // Basic Family Analysis Method
    private function analyzeFamilyRelationships($pengundis)
    {
        $this->familyGroups = [];
        $this->familySuggestions = [];
        
        if (empty($pengundis)) {
            return;
        }
        
        // Simple family grouping by similar names and addresses
        $groupId = 1;
        $processed = [];
        
        foreach ($pengundis as $index => $pengundi) {
            if (in_array($index, $processed)) {
                continue;
            }
            
            $familyMembers = [$pengundi];
            $processed[] = $index;
            
            // Find potential family members
            foreach ($pengundis as $otherIndex => $otherPengundi) {
                if ($index === $otherIndex || in_array($otherIndex, $processed)) {
                    continue;
                }
                
                // Check for similar surnames (last word in name)
                $name1Parts = explode(' ', trim($pengundi['Nama'] ?? ''));
                $name2Parts = explode(' ', trim($otherPengundi['Nama'] ?? ''));
                
                if (count($name1Parts) > 1 && count($name2Parts) > 1) {
                    $surname1 = end($name1Parts);
                    $surname2 = end($name2Parts);
                    
                    // Check for similar surnames or bin/binti relationships
                    $isSimilarSurname = strtolower($surname1) === strtolower($surname2);
                    $isBinBinti = $this->checkBinBintiRelation($pengundi['Nama'] ?? '', $otherPengundi['Nama'] ?? '');
                    $isSimilarAddress = $this->checkSimilarAddress($pengundi, $otherPengundi);
                    
                    if ($isSimilarSurname || $isBinBinti || $isSimilarAddress) {
                        $familyMembers[] = $otherPengundi;
                        $processed[] = $otherIndex;
                    }
                }
            }
            
            // Only create groups with more than 1 member
            if (count($familyMembers) > 1) {
                $this->familyGroups[] = [
                    'group_id' => $groupId,
                    'name' => "Keluarga {$groupId}",
                    'members' => array_map(function($member) {
                        return [
                            'name' => $member['Nama'] ?? '',
                            'no_kp' => $member['No_KP_Baru'] ?? '',
                            'relationship' => $this->guessRelationship($member)
                        ];
                    }, $familyMembers),
                    'confidence' => $this->calculateGroupConfidence($familyMembers)
                ];
                $groupId++;
            }
        }
        
        $this->showFamilyAnalysis = true;
    }
    
    private function checkBinBintiRelation($name1, $name2): bool
    {
        $name1Lower = strtolower($name1);
        $name2Lower = strtolower($name2);
        
        // Extract father's name from bin/binti pattern
        if (strpos($name1Lower, ' bin ') !== false) {
            $fatherName1 = trim(substr($name1Lower, strpos($name1Lower, ' bin ') + 5));
            if (strpos($name2Lower, $fatherName1) !== false) {
                return true;
            }
        }
        
        if (strpos($name1Lower, ' binti ') !== false) {
            $fatherName1 = trim(substr($name1Lower, strpos($name1Lower, ' binti ') + 7));
            if (strpos($name2Lower, $fatherName1) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    private function checkSimilarAddress($pengundi1, $pengundi2): bool
    {
        $addr1 = trim(($pengundi1['Alamat_1'] ?? '') . ' ' . ($pengundi1['Alamat_2'] ?? ''));
        $addr2 = trim(($pengundi2['Alamat_1'] ?? '') . ' ' . ($pengundi2['Alamat_2'] ?? ''));
        
        if (empty($addr1) || empty($addr2)) {
            return false;
        }
        
        // Simple similarity check - same first part of address
        $addr1Words = explode(' ', strtolower($addr1));
        $addr2Words = explode(' ', strtolower($addr2));
        
        if (count($addr1Words) > 2 && count($addr2Words) > 2) {
            $common = array_intersect(array_slice($addr1Words, 0, 3), array_slice($addr2Words, 0, 3));
            return count($common) >= 2;
        }
        
        return false;
    }
    
    private function guessRelationship($member): string
    {
        $name = strtolower($member['Nama'] ?? '');
        
        if (strpos($name, ' bin ') !== false) {
            return 'Anak lelaki';
        } elseif (strpos($name, ' binti ') !== false) {
            return 'Anak perempuan';
        } else {
            return 'Ahli keluarga';
        }
    }
    
    private function calculateGroupConfidence($members): int
    {
        // Simple confidence based on group size and name patterns
        $baseConfidence = count($members) > 2 ? 70 : 60;
        
        foreach ($members as $member) {
            $name = strtolower($member['Nama'] ?? '');
            if (strpos($name, ' bin ') !== false || strpos($name, ' binti ') !== false) {
                $baseConfidence += 10;
                break;
            }
        }
        
        return min($baseConfidence, 95);
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
                ->width('150px')
                ->fontFamily('mono')
                ->description(fn ($record) => $this->getFamilyRelationshipDescription($record)),

            TextColumn::make('Nama')
                ->label('Nama Pengundi')
                ->searchable()
                ->sortable()
                ->weight('bold')
                ->wrap()
                ->description(fn ($record) => $record->Keturunan ? "Keturunan: {$record->Keturunan}" : null),

            TextColumn::make('family_group')
                ->label('Kumpulan Keluarga')
                ->badge()
                ->color(fn ($record) => $this->getFamilyGroupColor($record))
                ->formatStateUsing(fn ($state, $record) => $this->getFamilyGroupName($record))
                ->visible(fn () => $this->showFamilyAnalysis)
                ->icon(fn ($record) => $this->getFamilyGroupId($record) ? 'heroicon-s-users' : null),

            TextColumn::make('Bangsa')
                ->label('Bangsa')
                ->badge()
                ->color('gray')
                ->default('--')
                ->toggleable(),

            TextColumn::make('Agama')
                ->label('Agama')
                ->badge()
                ->color('info')
                ->default('--')
                ->toggleable(),

            TextColumn::make('Kod_DUN')
                ->label('DUN')
                ->badge()
                ->color('primary')
                ->formatStateUsing(fn ($state) => "DUN {$state}")
                ->toggleable(),

            TextColumn::make('Kod_Daerah')
                ->label('Daerah')
                ->badge()
                ->color('warning')
                ->formatStateUsing(fn ($state) => "DR {$state}")
                ->toggleable(),

            TextColumn::make('Kod_Lokaliti')
                ->label('Lokaliti')
                ->badge()
                ->color('success')
                ->formatStateUsing(fn ($state) => "LK {$state}")
                ->toggleable(),
        ];
    }

    private function getFamilyRelationshipDescription($record): ?string
    {
        if (!$this->showFamilyAnalysis || empty($this->familyGroups) || !isset($record->No_KP_Baru)) {
            return null;
        }

        foreach ($this->familyGroups as $group) {
            foreach ($group['members'] as $member) {
                if ($member['no_kp'] === $record->No_KP_Baru) {
                    return $member['relationship'] ?? 'Ahli Keluarga';
                }
            }
        }

        return null;
    }

    private function getFamilyGroupColor($record): string
    {
        if (!$this->showFamilyAnalysis || !isset($record->No_KP_Baru)) {
            return 'gray';
        }

        $groupId = $this->getFamilyGroupId($record);
        if (!$groupId) {
            return 'gray';
        }
        
        $colors = ['success', 'warning', 'danger', 'info', 'primary'];
        
        return $colors[($groupId - 1) % count($colors)] ?? 'gray';
    }

    private function getFamilyGroupName($record): string
    {
        if (!$this->showFamilyAnalysis || !isset($record->No_KP_Baru)) {
            return '--';
        }

        $groupId = $this->getFamilyGroupId($record);
        return $groupId ? "Keluarga {$groupId}" : 'Tiada Kumpulan';
    }

    private function getFamilyGroupId($record): ?int
    {
        if (!isset($record->No_KP_Baru) || empty($this->familyGroups)) {
            return null;
        }
        
        foreach ($this->familyGroups as $group) {
            foreach ($group['members'] as $member) {
                if ($member['no_kp'] === $record->No_KP_Baru) {
                    return $group['group_id'];
                }
            }
        }
        return null;
    }

    // Disable pagination for family analysis
    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }

    // Table empty state
    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-home';
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'Sila pilih semua filter untuk mula';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Pilih DUN, Daerah, dan Lokaliti untuk melihat maklumat isi rumah.';
    }

    // Mount method to initialize form
    public function mount(): void
    {
        $this->form->fill([
            'dun_id' => $this->dun_id,
            'daerah_id' => $this->daerah_id,
            'lokaliti_id' => $this->lokaliti_id,
        ]);
    }

    // Page header actions
    protected function getHeaderActions(): array
    {
        return [
            Action::make('analyzeInfo')
                ->icon('heroicon-o-document-magnifying-glass')
                ->color('info')
                ->label('Analisis Maklumat')
                ->visible(fn () => $this->showResults)
                ->action(function () {
                    $pengundis = $this->getTableQuery()->get()->toArray();
                    $this->analyzeFamilyRelationships($pengundis);
                    
                    Notification::make()
                        ->success()
                        ->title('Analisis Maklumat Selesai')
                        ->body('Pengundi telah dikumpulkan berdasarkan potensi hubungan keluarga')
                        ->send();
                }),
            
            Action::make('analyzeAI')
                ->icon('heroicon-o-cpu-chip')
                ->color('warning')
                ->label('Analisis AI Keluarga')
                ->visible(fn () => $this->showResults && !empty($this->familyGroups))
                ->action(function () {
                    try {
                        $service = app(FamilyAnalysisService::class);
                        $pengundi = $this->getTableQuery()->get();
                        $analysis = $service->analyzeFamilyRelationships($pengundi->toArray());
                        
                        Notification::make()
                            ->success()
                            ->title('Analisis AI Selesai')
                            ->body($analysis['summary'] ?? 'Analisis hubungan keluarga telah selesai')
                            ->send();
                            
                        // Optionally store or display the detailed analysis
                        $this->aiAnalysisResult = $analysis;
                        
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Ralat Analisis AI')
                            ->body('Tidak dapat melakukan analisis AI: ' . $e->getMessage())
                            ->send();
                    }
                }),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->is_superadmin;
    }
}