<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Actions\Action;
use App\Models\User;

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
                //
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
