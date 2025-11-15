<?php

namespace App\Filament\Resources\CallRecords\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class CallRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([

                    TextColumn::make('user.name')
                        ->label('User')
                        ->sortable()
                        ->searchable()
                        ->toggleable(isToggledHiddenByDefault: auth()->user()?->is_admin),
                        
                    TextColumn::make('pengundi_ic')
                        ->label('IC Pengundi')
                        ->searchable()
                        ->sortable(),
                        
                    TextColumn::make('phone_number')
                        ->label('Nombor Telefon')
                        ->url(fn ($record) => $record->phone_number ? "tel:{$record->phone_number}" : null)
                        ->searchable()
                        ->badge()
                        ->sortable(),
                        TextColumn::make('phone_number')
                            ->label('Nombor Telefon')
                            ->url(fn ($record) => $record->phone_number ? "tel:" . preg_replace('/\D/', '', $record->phone_number) : null)
                            ->searchable()
                            ->badge()
                            ->sortable(),                    
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
                            'VR' => '🗻 GRS',
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
                        
                    TextColumn::make('created_at')
                        ->label('Dicipta')
                        ->dateTime('d/m/Y H:i')
                        ->since()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                        
                    TextColumn::make('updated_at')
                        ->label('Dikemaskini')
                        ->dateTime('d/m/Y H:i')
                        ->since()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
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
                        'VR' => '🗻 GRS',
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
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
