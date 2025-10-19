<?php

namespace App\Filament\Resources\Pengundis;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\Pengundis\Pages\CreatePengundi;
use App\Filament\Resources\Pengundis\Pages\EditPengundi;
use App\Filament\Resources\Pengundis\Pages\ListPengundis;
use App\Filament\Resources\Pengundis\Pages\ViewPengundi;
use App\Filament\Resources\Pengundis\Schemas\PengundiForm;
use App\Filament\Resources\Pengundis\Schemas\PengundiInfolist;
use App\Filament\Resources\Pengundis\Tables\PengundisTable;
use App\Models\Pengundi;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PengundiResource extends BaseResource
{
    protected static ?string $model = Pengundi::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Nama';

    // Disable tenant scoping for this resource
    protected static bool $isScopedToTenant = false;

    // If you only have read access to the database, you can enable this
    // protected static bool $canCreate = false;

    public static function form(Schema $schema): Schema
    {
        return PengundiForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PengundiInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PengundisTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->whereNotNull('No_KP_Baru')
            ->where('No_KP_Baru', '!=', '');
    }

    // If you only have read access, uncomment these methods:
    /*
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false; // Disable editing if no database write access
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false; // Disable deleting if no database write access
    }
    */

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPengundis::route('/'),
            'create' => CreatePengundi::route('/create'),
            'view' => ViewPengundi::route('/{record}'),
            'edit' => EditPengundi::route('/{record}/edit'),
        ];
    }
}
