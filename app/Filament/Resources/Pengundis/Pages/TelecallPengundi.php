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
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Textarea;

class TelecallPengundi extends ViewRecord
{
    protected static string $resource = PengundiResource::class;

    protected string $view = 'filament.resources.pengundis.pages.telecall-pengundi';

    public function getTitle(): string
    {
        return 'Telecall: ' . ($this->record->Nama ?? 'Pengundi');
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make()
                    ->schema([
                        Grid::make()
                            ->schema([
                                // Section 1: Info Pengundi
                                Section::make('Maklumat Pengundi')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('Nama')
                                                    ->label('Nama')
                                                    ->weight('bold'),
                                                    
                                                TextEntry::make('No_KP_Baru')
                                                    ->label('No. KP'),
                                                    
                                                TextEntry::make('umur_jantina')
                                                    ->label('Umur & Jantina')
                                                    ->state(function ($record) {
                                                        $umur = now()->year - (2000 + (int)substr($record->No_KP_Baru, 0, 2));
                                                        if ($umur < 18) $umur += 100;
                                                        $jantina = ((int)substr($record->No_KP_Baru, -2) % 2 === 0) ? 'Perempuan' : 'Lelaki';
                                                        return $umur . ' tahun | ' . $jantina;
                                                    }),
                                            ]),
                                            
                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('Agama')
                                                    ->label('Agama')
                                                    ->placeholder('-'),
                                                    
                                                TextEntry::make('Keturunan')
                                                    ->label('Keturunan')
                                                    ->placeholder('-'),
                                                    
                                                TextEntry::make('Bangsa')
                                                    ->label('Bangsa')
                                                    ->placeholder('-'),
                                            ]),
                                            
                                        TextEntry::make('phone_numbers')
                                            ->label('Nombor Telefon')
                                            ->state(function ($record) {
                                                $phoneNumbers = TelecallService::getPhoneNumbers($record);
                                                return !empty($phoneNumbers) ? implode(', ', $phoneNumbers) : 'Tiada nombor telefon';
                                            })
                                            ->badge()
                                            ->separator(','),
                                    ]),
                                // Section 3: Rekod Culaan
                                Section::make('Rekod Culaan')
                                    ->schema([
                                        ToggleButtons::make('Kod_cula')
                                            ->inline()
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
                                            ]),
                                        Textarea::make('Catatan')
                                            ->label('Catatan')
                                            ->rows(2)
                                            ->autosize()
                                            ->default($this->record->Catatan),
                                    ]),
                            ])
                            ->columns(1)
                            ->columnSpan(3),
                        // Right: Skrip Panggilan
                        Section::make('Skrip Panggilan')
                            ->schema([
                                ViewEntry::make('skrip')
                                    ->view('filament.infolists.telecall-script')
                                    ->state(fn() => TelecallService::getSkripPanggilan()),
                            ])
                            ->columnSpan(7),
                    ])
                    ->columns(10)
            ])
            ->columns(1);
    }

}
