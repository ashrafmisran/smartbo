<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Actions\Action;
use App\Models\User;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(User::query()->where('is_superadmin', false))
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->description(fn ($record) => $record->email)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status'),
                BooleanColumn::make('is_admin')
                    ->label('Admin')
                    ->sortable(),
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
                Action::make('Gantung pengguna')
                    ->action(function ($record) {
                        User::find($record->id)->update(['status' => 'suspended']);
                    })
                    ->label('Gantung pengguna')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status == 'verified'),
                Action::make('Aktifkan semula pengguna')
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
                Action::make('Tamatkan peranan admin')
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
