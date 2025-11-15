<?php

namespace App\Filament\Resources\CallRecords;

use App\Filament\Resources\CallRecords\Pages\CreateCallRecord;
use App\Filament\Resources\CallRecords\Pages\EditCallRecord;
use App\Filament\Resources\CallRecords\Pages\ListCallRecords;
use App\Filament\Resources\CallRecords\Schemas\CallRecordForm;
use App\Filament\Resources\CallRecords\Tables\CallRecordsTable;
use App\Models\CallRecord;
use BackedEnum;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CallRecordResource extends Resource
{
    protected static ?string $model = CallRecord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'phone_number';

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return "/bo/senarai-pengundi/{$record->pengundi_ic}/telecall";
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Masa' => $record->called_at->diffForHumans(),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // If user is not admin, filter to show only their records
        if (!auth()->user()?->is_admin) {
            $query->where('user_id', auth()->id());
        }
        
        return $query;
    }

    public static function canViewAny(): bool
    {
        // Allow access if user is not pending
        return auth()->user()->status !== 'pending';
    }

    public static function form(Schema $schema): Schema
    {
        return CallRecordForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CallRecordsTable::configure($table);
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
            'index' => ListCallRecords::route('/'),
            // 'create' => CreateCallRecord::route('/create'),
            // 'edit' => EditCallRecord::route('/{record}/edit'),
        ];
    }
}
