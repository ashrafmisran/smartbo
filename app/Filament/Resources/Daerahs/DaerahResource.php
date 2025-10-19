<?php

namespace App\Filament\Resources\Daerahs;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\Daerahs\Pages\CreateDaerah;
use App\Filament\Resources\Daerahs\Pages\EditDaerah;
use App\Filament\Resources\Daerahs\Pages\ListDaerahs;
use App\Filament\Resources\Daerahs\Pages\ViewDaerah;
use App\Filament\Resources\Daerahs\Schemas\DaerahForm;
use App\Filament\Resources\Daerahs\Schemas\DaerahInfolist;
use App\Filament\Resources\Daerahs\Tables\DaerahsTable;
use App\Models\Daerah;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DaerahResource extends BaseResource
{
    protected static ?string $model = Daerah::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Nama_Daerah';

    // Disable tenant scoping for this resource
    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return DaerahForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DaerahInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DaerahsTable::configure($table);
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
            'index' => ListDaerahs::route('/'),
            'create' => CreateDaerah::route('/create'),
            'view' => ViewDaerah::route('/{record}'),
            'edit' => EditDaerah::route('/{record}/edit'),
        ];
    }
}
