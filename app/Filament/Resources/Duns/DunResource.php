<?php

namespace App\Filament\Resources\Duns;

use App\Filament\Resources\Duns\Pages\CreateDun;
use App\Filament\Resources\Duns\Pages\EditDun;
use App\Filament\Resources\Duns\Pages\ListDuns;
use App\Filament\Resources\Duns\Pages\ViewDun;
use App\Filament\Resources\Duns\Schemas\DunForm;
use App\Filament\Resources\Duns\Schemas\DunInfolist;
use App\Filament\Resources\Duns\Tables\DunsTable;
use App\Models\Dun;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DunResource extends Resource
{
    protected static ?string $model = Dun::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Nama_DUN';

    // Disable tenant scoping for this resource
    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return DunForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DunInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DunsTable::configure($table);
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
            'index' => ListDuns::route('/'),
            'create' => CreateDun::route('/create'),
            'view' => ViewDun::route('/{record}'),
            'edit' => EditDun::route('/{record}/edit'),
        ];
    }
}
