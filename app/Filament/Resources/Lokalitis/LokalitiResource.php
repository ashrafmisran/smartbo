<?php

namespace App\Filament\Resources\Lokalitis;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\Lokalitis\Pages\CreateLokaliti;
use App\Filament\Resources\Lokalitis\Pages\EditLokaliti;
use App\Filament\Resources\Lokalitis\Pages\ListLokalitis;
use App\Filament\Resources\Lokalitis\Pages\ViewLokaliti;
use App\Filament\Resources\Lokalitis\Schemas\LokalitiForm;
use App\Filament\Resources\Lokalitis\Schemas\LokalitiInfolist;
use App\Filament\Resources\Lokalitis\Tables\LokalitisTable;
use App\Models\Lokaliti;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LokalitiResource extends BaseResource
{
    protected static ?string $model = Lokaliti::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Nama_Lokaliti';

    // Disable tenant scoping for this resource
    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return LokalitiForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LokalitiInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LokalitisTable::configure($table);
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
            'index' => ListLokalitis::route('/'),
            'create' => CreateLokaliti::route('/create'),
            'view' => ViewLokaliti::route('/{record}'),
            'edit' => EditLokaliti::route('/{record}/edit'),
        ];
    }
}
