<?php

namespace App\Filament\Resources\Pengundis\Pages;

use App\Filament\Resources\Pengundis\PengundiResource;
use App\Services\TelecallService;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Alignment;
use Filament\Actions\Action;


class TelecallPengundi extends ViewRecord
{
    protected static string $resource = PengundiResource::class;

    protected string $view = 'filament.resources.pengundis.pages.telecall-pengundi';
    
    public $kod_cula = '';
    public $catatan = '';
    
    public function mount(string|int $record): void
    {
        parent::mount($record);
        
        $this->kod_cula = $this->record->Kod_Cula ?? '';
        $this->catatan = $this->record->Catatan ?? '';
    }
    
    public function saveCulaan()
    {
        $this->record->update([
            'Kod_Cula' => $this->kod_cula,
            'Catatan' => $this->catatan,
        ]);
        
        Notification::make()
            ->title('Culaan disimpan!')
            ->success()
            ->send();
    }

    public function getTitle(): string
    {
        return 'Telecall: ' . ($this->record->Nama ?? 'Pengundi');
    }

    protected function copyToClipboard($number)
    {
        Notification::make()
            ->title('Nombor disalin!')
            ->body('Nombor telefon ' . $number . ' telah disalin ke clipboard.')
            ->success()
            ->send();
            
        // Note: Actual clipboard copying would require JavaScript
        // This just shows a notification for now
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Maklumat Pengundi')
                    ->icon('heroicon-o-information-circle')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('Nama')
                            ->label('Nama')
                            ->weight('bold')
                            ->inlineLabel()
                            ->columnSpan(3)
                            ->alignStart(),
                        
                        TextEntry::make('Kod_Cula')
                            ->label('Culaan semasa')
                            ->inlineLabel()
                            ->weight('bold')
                            ->placeholder('Tiada culaan'),
                            
                        TextEntry::make('No_KP_Baru')
                            ->label('No. KP')
                            ->inlineLabel()
                            ->weight('bold'),
                            
                        TextEntry::make('umur')
                            ->label('Umur')
                            ->state(function ($record) {
                                $umur = now()->year - (2000 + (int)substr($record->No_KP_Baru, 0, 2));
                                if ($umur < 18) $umur += 100;
                                return $umur . ' tahun';
                            })
                            ->inlineLabel()
                            ->weight('bold'),
                            
                        TextEntry::make('jantina')
                            ->label('Jantina')
                            ->state(function ($record) {
                                return ((int)substr($record->No_KP_Baru, -2) % 2 === 0) ? 'Perempuan' : 'Lelaki';
                            })
                            ->inlineLabel()
                            ->weight('bold'),

                        TextEntry::make('negeri_kelahiran')
                            ->label('Negeri Kelahiran')
                            ->state(function ($record) {
                                $birthStateCode = substr($record->No_KP_Baru, 6, 2);
                                return match($birthStateCode) {
                                    '01', '21', '22', '23', '24' => 'Johor',
                                    '02', '25', '26', '27' => 'Kedah',
                                    '03', '28', '29' => 'Kelantan',
                                    '04', '30' => 'Melaka',
                                    '05', '31', '59' => 'Negeri Sembilan',
                                    '06', '32', '33' => 'Pahang',
                                    '07', '34', '35' => 'Pulau Pinang',
                                    '08', '36', '37', '38', '39' => 'Perak',
                                    '09', '40' => 'Perlis',
                                    '10', '41', '42', '43', '44' => 'Selangor',
                                    '11', '45', '46' => 'Terengganu',
                                    '12', '47', '48', '49' => 'Sabah',
                                    '13', '50', '51', '52', '53' => 'Sarawak',
                                    '14', '54', '55', '56', '57' => 'Wilayah Persekutuan (Kuala Lumpur)',
                                    '15', '58' => 'Wilayah Persekutuan (Labuan)',
                                    '16' => 'Wilayah Persekutuan (Putrajaya)',
                                    '82' => 'Negeri Tidak Diketahui',
                                    default => 'Tidak Diketahui'
                                };
                            })
                            ->inlineLabel()
                            ->weight('bold'),

                        TextEntry::make('Agama')
                            ->label('Agama')
                            ->placeholder('-')
                            ->inlineLabel()
                            ->weight('bold'),
                            
                        TextEntry::make('Keturunan')
                            ->label('Bangsa')
                            ->state(function ($record) {
                                $keturunan = $record->Keturunan ?? '';
                                return match(strtoupper($keturunan)) {
                                    'M' => 'Melayu',
                                    'C' => 'Cina',
                                    'I' => 'India',
                                    'L' => 'Lain-lain',
                                    default => $keturunan ?: '-'
                                };
                            })
                            ->inlineLabel()
                            ->weight('bold'),
                            
                        TextEntry::make('Bangsa')
                            ->label('Etnik')
                            ->placeholder('-')
                            ->inlineLabel()
                            ->weight('bold'),
                            
                        // RepeatableEntry::make('phone_numbers')
                        //     ->label('Nombor Telefon')
                        //     ->getStateUsing(fn ($record) => TelecallService::getPhoneNumbers($record))
                        //     ->schema([
                        //         TextEntry::make('display')
                        //             ->hiddenLabel()
                        //             ->weight('medium')
                        //             ->color('primary')
                        //             ->icon('heroicon-o-phone')
                        //             ->iconPosition('before')
                        //             ->url(fn (Get $get) => 'tel:' . preg_replace('/\D+/', '', $get('number')))
                        //             ->openUrlInNewTab(false)
                        //             ->extraAttributes([
                        //                 'class' => 'inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-primary-600 hover:text-primary-500 hover:bg-gray-50 rounded-lg transition-colors duration-200',
                        //                 'onclick' => 'return confirm("Panggil ' . '" + this.textContent.trim() + "?")',
                        //             ]),
                        //     ])
                        //     ->inlineLabel()
                        //     ->grid(4)
                        //     ->contained(false)
                        //     ->columnSpanFull(),
                    ]),

                Grid::make()
                    ->schema([
                        Section::make('Skrip Panggilan')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                ViewEntry::make('skrip')
                                    ->view('filament.infolists.telecall-script')
                                    ->state(fn() => TelecallService::getSkripPanggilan()),
                            ])
                            ->columnSpan(8),

                        // Rekod culaan
                        Section::make('Rekod Culaan')
                            ->schema([
                                ViewEntry::make('culaan_form')
                                    ->view('filament.infolists.telecall-simple-form'),
                                TextEntry::make('current_cula_display')
                                    ->label('Culaan Semasa')
                                    ->state(function ($record) {
                                        $culaOptions = [
                                            "VA" => "🤷🏻‍♂️ Atas Pagar",
                                            "VB" => "💚 Undi Bulan",
                                            "VC" => "⚪ Condong Bulan", 
                                            "VD" => "⚖️ BN",
                                            "VN" => "🚀 PH",
                                            "VS" => "🪢 PN",
                                            "VT" => "🪢 Rakan PN",
                                            "VR" => "🗻 GRS",
                                            "VW" => "❌ Salah nombor",
                                            "VX" => "📵 Tiada jawapan",
                                            "VY" => "🙅🏻‍♂️ Enggan respon",
                                            "VZ" => "💆🏻‍♂️ Benci politik"
                                        ];
                                        return $record->Kod_Cula ? ($culaOptions[$record->Kod_Cula] ?? $record->Kod_Cula) : 'Tiada culaan';
                                    }),
                                TextEntry::make('Catatan')
                                    ->label('Catatan')
                                    ->placeholder('Tiada catatan')
                                    ->limit(100),
                            ])
                            ->columnSpan(4),
                    ])
                    ->columns(12)
            ])
            ->columns(1);
    }

}
