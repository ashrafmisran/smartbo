<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use App\Models\User;
use App\Models\Kawasan;
use Carbon\Carbon;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->where('is_superadmin', false)
                    ->with('divisionKawasan')
                    ->when(!auth()->user()?->is_superadmin, function ($query) {
                        // Get the authenticated user's state
                        $currentUserState = auth()->user()?->divisionKawasan?->negeri;
                        
                        if ($currentUserState) {
                            // Filter users to same state as authenticated user
                            $query->whereHas('divisionKawasan', function ($q) use ($currentUserState) {
                                $q->where('negeri', $currentUserState);
                            });
                        } else {
                            // If current user has no state assigned, show no users (except for superadmin)
                            $query->whereRaw('1 = 0');
                        }
                    })
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                Split::make([

                    TextColumn::make('name')
                        ->label('Name')
                        ->description(fn ($record) => $record->email)
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('divisionKawasan.name')
                        ->label('Kawasan')
                        ->searchable()
                        ->description(fn ($record) => $record->pas_membership_no)
                        ->sortable(),
                    TextColumn::make('status')
                        ->label('Status')
                        ->searchable()
                        ->sortable()
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'pending' => 'Tunggu pengesahan',
                            'verified' => 'Disahkan',
                            'suspended' => 'Digantung',
                            default => $state,
                        })
                        ->color(fn (string $state): string => match ($state) {
                            'pending' => 'gray',
                            'verified' => 'success',
                            'suspended' => 'danger',
                            default => 'gray',
                        }),
                    TextColumn::make('is_admin')
                        ->label('Role')
                        ->formatStateUsing(fn (bool $state): string => $state ? 'Admin' : 'Pengguna')
                        ->badge()
                        ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                        ->sortable(),
                    TextColumn::make('created_at')
                        ->label('Masa Daftar')
                        ->since()
                        ->sortable(),

                ])
                ->from('md')
            ])
            ->filters([
                SelectFilter::make('kawasan')
                    ->label('Kawasan')
                    ->relationship('divisionKawasan', 'name')
                    ->searchable()
                    ->options(function () {
                        return Kawasan::orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->visible(fn () => auth()->user()?->is_superadmin),
                SelectFilter::make('is_admin')
                    ->label('Role')
                    ->options([
                        '1' => 'Admin',
                        '0' => 'Pengguna',
                    ])
                    ->query(function ($query, $data) {
                        if ($data['value'] !== null) {
                            $query->where('is_admin', $data['value']);
                        }
                    }),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Tunggu pengesahan',
                        'verified' => 'Disahkan',
                        'suspended' => 'Digantung',
                    ]),
                SelectFilter::make('registered_on')
                    ->label('Daftar pada')
                    ->options([
                        'today' => 'Hari ini',
                        'yesterday' => 'Semalam',
                        'this_week' => 'Minggu ini',
                        'this_month' => 'Bulan ini',
                        'last_month' => 'Bulan lepas',
                    ])
                    ->query(function ($query, $data) {
                        if (!$data['value']) {
                            return;
                        }

                        $now = Carbon::now();
                        
                        switch ($data['value']) {
                            case 'today':
                                $query->whereDate('created_at', $now->toDateString());
                                break;
                            case 'yesterday':
                                $query->whereDate('created_at', $now->subDay()->toDateString());
                                break;
                            case 'this_week':
                                $query->whereBetween('created_at', [
                                    $now->startOfWeek()->toDateString(),
                                    $now->endOfWeek()->toDateString()
                                ]);
                                break;
                            case 'this_month':
                                $query->whereMonth('created_at', $now->month)
                                      ->whereYear('created_at', $now->year);
                                break;
                            case 'last_month':
                                $lastMonth = $now->subMonth();
                                $query->whereMonth('created_at', $lastMonth->month)
                                      ->whereYear('created_at', $lastMonth->year);
                                break;
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Sunting'),
                Action::make('Sahkan pengguna')
                    ->action(function ($record) {
                        User::find($record->id)->update(['status' => 'verified']);
                    })
                    ->label('Sahkan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status == 'pending'),
                Action::make('Gantung')
                    ->action(function ($record) {
                        User::find($record->id)->update(['status' => 'suspended']);
                        User::find($record->id)->update(['is_admin' => false]);
                    })
                    ->label('Gantung')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status == 'verified'),
                Action::make('Aktifkan semula')
                    ->action(function ($record) {
                        User::find($record->id)->update(['status' => 'verified']);
                    })
                    ->label('Aktifkan semula')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status == 'suspended'),
                Action::make('Lantik sebagai admin')
                    ->action(function ($record) {
                        User::find($record->id)->update(['is_admin' => true]);
                    })
                    ->label('Lantik admin')
                    ->icon('heroicon-o-shield-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => !$record->is_admin && $record->status == 'verified'),
                Action::make('Pecat admin')
                    ->action(function ($record) {
                        User::find($record->id)->update(['is_admin' => false]);
                    })
                    ->requiresConfirmation()
                    ->label('Pecat admin')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('danger')
                    ->visible(fn ($record) => $record->is_admin),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
