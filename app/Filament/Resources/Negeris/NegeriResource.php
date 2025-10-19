<?php

namespace App\Filament\Resources\Negeris;

use App\Filament\Resources\Negeris\Pages\CreateNegeri;
use App\Filament\Resources\Negeris\Pages\EditNegeri;
use App\Filament\Resources\Negeris\Pages\ListNegeris;
use App\Filament\Resources\Negeris\Pages\ViewNegeri;
use App\Filament\Resources\Negeris\Schemas\NegeriForm;
use App\Filament\Resources\Negeris\Schemas\NegeriInfolist;
use App\Filament\Resources\Negeris\Tables\NegerisTable;
use App\Models\Negeri;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NegeriResource extends Resource
{
    protected static ?string $model = Negeri::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Nama_Negeri';

    // Disable tenant scoping for this resource
    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return NegeriForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return NegeriInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NegerisTable::configure($table);
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
            'index' => ListNegeris::route('/'),
            'create' => CreateNegeri::route('/create'),
            'view' => ViewNegeri::route('/{record}'),
            'edit' => EditNegeri::route('/{record}/edit'),
        ];
    }
}
