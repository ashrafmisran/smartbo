<?php

namespace App\Filament\Resources\Databases\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\User;
use Filament\Notifications\Notification;

class DatabasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('alias')
                    ->searchable()
                    ->label('Database')
                    ->description(fn ($record) => $record->name),
                TextColumn::make('host')
                    ->searchable(),
                TextColumn::make('port')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('users_count')
                    ->label('Users')
                    ->counts('users')
                    ->badge()
                    ->color('success'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->iconButton(),
                EditAction::make()->iconButton(),
                Action::make('manageUsers')
                    ->label('Manage Users')
                    ->icon('heroicon-o-users')
                    ->iconButton()
                    ->form([
                        Select::make('users')
                            ->label('Select Users')
                            ->multiple()
                            ->options(User::all()->pluck('name', 'id'))
                            ->default(fn ($record) => $record->users->pluck('id')->toArray())
                            ->searchable()
                            ->preload()
                    ])
                    ->action(function ($record, array $data) {
                        $record->users()->sync($data['users'] ?? []);

                        Notification::make()
                            ->title('Users updated successfully')
                            ->success()
                            ->send();
                    })
                    ->modalHeading(fn ($record) => "Manage Users for {$record->alias}")
                    ->modalDescription('Select users who should have access to this database.')
                    ->modalSubmitActionLabel('Update Users'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
