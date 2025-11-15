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
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Alignment;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;


class TelecallPengundi extends ViewRecord
{
    protected static string $resource = PengundiResource::class;

    protected string $view = 'filament.resources.pengundis.pages.telecall-pengundi';
    
    public $culaan_data = [
        'kod_cula' => '',
        'catatan' => '',
    ];
    
    public function mount(string|int $record): void
    {
        parent::mount($record);
        
        $this->culaan_data = [
            'kod_cula' => $this->record->Kod_Cula ?? '',
            'catatan' => $this->record->Catatan ?? '',
        ];
    }
    
    public function getTitle(): string
    {
        return 'Telecall: ' . ($this->record->Nama ?? 'Pengundi');
    }
    
    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }

    public function recordCall($phoneNumber)
    {
        try {
            // Record the call in call_records table
            DB::table('call_records')->insert([
                'user_id' => auth()->id(),
                'pengundi_ic' => $this->record->No_KP_Baru,
                'phone_number' => $phoneNumber,
                'called_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            Notification::make()
                ->title('Rekod panggilan disimpan!')
                ->body('Panggilan ke ' . $phoneNumber . ' telah direkodkan.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Ralat!')
                ->body('Gagal merekod panggilan: ' . $e->getMessage())
                ->danger()
                ->send();
        }
        
        return back();
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
                                
                                $kodCula = trim($record->Kod_Cula ?? '');
                                
                                if (empty($kodCula)) {
                                    return 'Tiada culaan';
                                }
                                
                                return $culaOptions[$kodCula] ?? ('Unknown: ' . $kodCula);
                            })
                            ->label('Culaan semasa')
                            ->inlineLabel()
                            ->weight('bold')
                            ->placeholder('Tiada data')
                            ->default('Tiada culaan'),
                            
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
                            
                    ]),
                
                Section::make('Nombor Telefon (Tekan nombor untuk push to phone)')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        RepeatableEntry::make('phone_numbers')
                            ->hiddenLabel()
                            ->getStateUsing(fn ($record) => TelecallService::getPhoneNumbers($record))
                            ->schema([
                                TextEntry::make('display')
                                    ->hiddenLabel()
                                    ->weight('medium')
                                    ->color('primary')
                                    ->icon('heroicon-o-phone')
                                    ->iconPosition('before')
                                    ->action(
                                        Action::make('recordCall')
                                            ->action(function (string $state) {
                                                try {
                                                    // Record the call in call_records table
                                                    DB::table('call_records')->insert([
                                                        'user_id' => auth()->id(),
                                                        'pengundi_ic' => $this->record->No_KP_Baru,
                                                        'phone_number' => $state,
                                                        'called_at' => now(),
                                                        'created_at' => now(),
                                                        'updated_at' => now(),
                                                    ]);

                                                    Notification::make()
                                                        ->title('Rekod panggilan disediakan!')
                                                        ->body('Rekod panggilan ke ' . $state . ' telah disediakan dalam pengkalan data. Sila masukkan keputusan cula selepas panggilan selesai.')
                                                        ->success()
                                                        ->send();

                                                    // Add mobile redirect to tel: link for mobile devices
                                                    if (request()->header('User-Agent') && 
                                                        (str_contains(strtolower(request()->header('User-Agent')), 'mobile') ||
                                                         str_contains(strtolower(request()->header('User-Agent')), 'android') ||
                                                         str_contains(strtolower(request()->header('User-Agent')), 'iphone'))) {
                                                        
                                                        $cleanNumber = preg_replace('/[^0-9]/', '', $state);
                                                        $this->js("window.open('tel:$cleanNumber', '_self');");
                                                    }

                                                } catch (\Exception $e) {
                                                    Notification::make()
                                                        ->title('Ralat!')
                                                        ->body('Gagal merekod panggilan: ' . $e->getMessage())
                                                        ->danger()
                                                        ->send();
                                                }
                                            })
                                    )
                            ])
                            ->grid(4)
                            ->contained(false)
                            ->columnSpanFull()
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
                                    })
                                    ->weight('bold')
                                    ->color(fn ($record) => $record->Kod_Cula ? 'success' : 'gray'),
                                    
                                TextEntry::make('Catatan')
                                    ->label('Catatan')
                                    ->placeholder('Tiada catatan')
                                    ->limit(100)
                                    ->color('gray'),
                                
                                    Action::make('edit_culaan')
                                        ->label('Edit Culaan')
                                        ->icon('heroicon-o-pencil')
                                        ->color('primary')
                                        ->form([
                                            Radio::make('kod_cula')
                                                ->label('Culaan')
                                                ->options([
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
                                                ])
                                                ->columns(2)
                                                ->required(),
                                                
                                            Textarea::make('catatan')
                                                ->label('Catatan')
                                                ->rows(3)
                                                ->placeholder('Masukkan catatan...'),
                                        ])
                                        ->fillForm(function () {
                                            return [
                                                'kod_cula' => $this->record->Kod_Cula ?? '',
                                                'catatan' => $this->record->Catatan ?? '',
                                            ];
                                        })
                                        ->action(function (array $data) {
                                            try {
                                                // Debug logging
                                                \Log::info('TelecallPengundi Action Data:', [
                                                    'data' => $data,
                                                    'record_kp' => $this->record->No_KP_Baru,
                                                    'current_cula' => $this->record->Kod_Cula,
                                                    'current_catatan' => $this->record->Catatan,
                                                ]);
                                                
                                                // Use direct DB update
                                                $updated = DB::connection('ssdp')
                                                    ->table('daftara')
                                                    ->where('No_KP_Baru', $this->record->No_KP_Baru)
                                                    ->update([
                                                        'Kod_Cula' => $data['kod_cula'] ?? '',
                                                        'Catatan' => $data['catatan'] ?? '',
                                                    ]);
                                                
                                                \Log::info('DB Update Result:', [
                                                    'rows_affected' => $updated,
                                                    'update_data' => [
                                                        'Kod_Cula' => $data['kod_cula'] ?? '',
                                                        'Catatan' => $data['catatan'] ?? '',
                                                    ]
                                                ]);
                                                // Record the telecall activity in call_records
                                                $existingCallRecord = DB::table('call_records')
                                                    ->where('pengundi_ic', $this->record->No_KP_Baru)
                                                    ->where('user_id', auth()->id())
                                                    ->orderBy('created_at', 'desc')
                                                    ->first();

                                                if ($existingCallRecord) {
                                                    // Update existing call record
                                                    DB::table('call_records')
                                                        ->where('id', $existingCallRecord->id)
                                                        ->update([
                                                            'kod_cula' => $data['kod_cula'] ?? '',
                                                            'notes' => $data['catatan'] ?? '',
                                                            'updated_at' => now(),
                                                        ]);
                                                } else {
                                                    // Insert new call record
                                                    DB::table('call_records')->insert([
                                                        'user_id' => auth()->id(),
                                                        'pengundi_ic' => $this->record->No_KP_Baru,
                                                        'phone_number' => '', // We can add this if needed
                                                        'kod_cula' => $data['kod_cula'] ?? '',
                                                        'notes' => $data['catatan'] ?? '',
                                                        'called_at' => now(),
                                                        'created_at' => now(),
                                                        'updated_at' => now(),
                                                    ]);
                                                }

                                                if ($updated) {
                                                    // Verify the update by re-reading from database
                                                    $verifyRecord = DB::connection('ssdp')
                                                        ->table('daftara')
                                                        ->where('No_KP_Baru', $this->record->No_KP_Baru)
                                                        ->select('Kod_Cula', 'Catatan')
                                                        ->first();
                                                    
                                                    \Log::info('Verification Result:', [
                                                        'db_kod_cula' => $verifyRecord->Kod_Cula ?? 'NULL',
                                                        'db_catatan' => $verifyRecord->Catatan ?? 'NULL',
                                                    ]);
                                                    
                                                    // Update local record
                                                    $this->record->Kod_Cula = $data['kod_cula'] ?? '';
                                                    $this->record->Catatan = $data['catatan'] ?? '';
                                                    $this->culaan_data = $data;
                                                    
                                                    Notification::make()
                                                        ->title('Culaan disimpan!')
                                                        ->body('Kod Cula: ' . ($data['kod_cula'] ?? 'Kosong') . ', Catatan: ' . ($data['catatan'] ?: 'Tiada'))
                                                        ->success()
                                                        ->send();
                                                        
                                                    // Modal will close automatically after successful action
                                                } else {
                                                    throw new \Exception('Tiada baris yang dikemaskini - mungkin No_KP_Baru tidak dijumpai');
                                                }
                                            } catch (\Exception $e) {
                                                \Log::error('Error saving culaan: ' . $e->getMessage(), [
                                                    'data' => $data ?? 'No data',
                                                    'record_kp' => $this->record->No_KP_Baru ?? 'No KP',
                                                    'stack_trace' => $e->getTraceAsString(),
                                                ]);
                                                
                                                Notification::make()
                                                    ->title('Ralat!')
                                                    ->body('Terdapat masalah: ' . $e->getMessage())
                                                    ->danger()
                                                    ->send();
                                            }
                                        })
                                        ->modalHeading('Edit Culaan')
                                        ->modalSubmitActionLabel('Simpan')
                                        ->modalCancelActionLabel('Batal'),

                            ])
                            ->columnSpan(4),
                    ])
                    ->columns(12)
            ])
            ->columns(1);
    }

}
