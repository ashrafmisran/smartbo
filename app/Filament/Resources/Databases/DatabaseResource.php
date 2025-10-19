<?php

namespace App\Filament\Resources\Databases;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\Databases\Pages\CreateDatabase;
use App\Filament\Resources\Databases\Pages\EditDatabase;
use App\Filament\Resources\Databases\Pages\ListDatabases;
use App\Filament\Resources\Databases\Pages\ViewDatabase;
use App\Filament\Resources\Databases\Schemas\DatabaseForm;
use App\Filament\Resources\Databases\Schemas\DatabaseInfolist;
use App\Filament\Resources\Databases\Tables\DatabasesTable;
use App\Models\Database;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DatabaseResource extends BaseResource
{
    protected static ?string $model = Database::class;

    // Disable tenant scoping for this resource
    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'alias';

    public static function form(Schema $schema): Schema
    {
        return DatabaseForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DatabaseInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DatabasesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDatabases::route('/'),
            'create' => CreateDatabase::route('/create'),
            'view' => ViewDatabase::route('/{record}'),
            'edit' => EditDatabase::route('/{record}/edit'),
        ];
    }
}
