<?php

namespace App\Filament\Widgets;

use App\Models\CallRecord;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;

class DailyRecordStat extends TableWidget
{
    protected ?string $selectedDate = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $date = $this->selectedDate ?? Carbon::today()->toDateString();

                return CallRecord::query()
                    ->whereDate('created_at', $date)
                    ->selectRaw('MAX(id) as id, kod_cula as vcc_kod_cula, COUNT(*) as total_count')
                    ->groupBy('kod_cula')
                    ->orderByDesc('total_count');
            })
            ->columns([
                TextColumn::make('vcc_kod_cula')
                    ->label('VCC Kod_Cula'),
                TextColumn::make('total_count')
                    ->label('Bil. Rekod'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('date_filter')
                    ->label(fn (): string => $this->getDateFilterLabel())
                    ->form([
                        Select::make('date')
                            ->label('Pilih tarikh')
                            ->options(fn (): array => $this->getDateOptions())
                            ->default($this->selectedDate ?? Carbon::today()->toDateString())
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $this->selectedDate = $data['date'] ?? null;
                    }),
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    protected function getDateOptions(): array
    {
        $start = Carbon::create(2026, 7, 10);
        $end = Carbon::today();

        if ($start->gt($end)) {
            $start = $end;
        }

        $options = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $options[$current->toDateString()] = $current->locale('ms')->translatedFormat('d/m/Y (l)');
            $current->addDay();
        }

        return array_reverse($options, true);
    }

    protected function getDateFilterLabel(): string
    {
        $date = $this->selectedDate ?? Carbon::today()->toDateString();

        return 'Tarikh: '.Carbon::parse($date)->locale('ms')->translatedFormat('d/m/Y (l)');
    }
}
