<?php

namespace App\Filament\Widgets;

use App\Models\CallRecord;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class DailyRecordStat extends Widget implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.widgets.daily-record-stat';

    public ?array $data = [];

    public ?string $selectedDate = null;

    public function mount(): void
    {
        $this->selectedDate = Carbon::today()->toDateString();

        $this->form->fill([
            'selectedDate' => $this->selectedDate,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('selectedDate')
                    ->label('Tarikh')
                    ->options($this->getDateOptions())
                    ->live(),
            ])
            ->statePath('data');
    }

    public function getRecords()
    {
        $date = $this->data['selectedDate'] ?? $this->selectedDate;

        $records = CallRecord::query()
            ->where('user_id', auth()->id())
            ->whereDate('created_at', $date)
            ->selectRaw('
                kod_cula,
                COUNT(*) as total_count
            ')
            ->groupBy('kod_cula')
            ->orderByDesc('total_count')
            ->get();

        $culaNameMap = $this->getCulaNameMap();

        return $records->map(function ($record) use ($culaNameMap) {
            $record->nama_cula = $this->getCulaLabel($record->kod_cula, $culaNameMap);

            return $record;
        });
    }

    protected function getCulaNameMap(): array
    {
        try {
            return DB::connection('ssdp')
                ->table('cula')
                ->pluck('Nama_Cula', 'Kod_Cula')
                ->mapWithKeys(fn ($name, $code) => [trim((string) $code) => trim((string) $name)])
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getCulaLabel(?string $kodCula, ?array $nameMap = null): string
    {
        $code = trim((string) ($kodCula ?? ''));

        if ($code === '') {
            return 'Tiada cula';
        }

        $nameMap = $nameMap ?? $this->getCulaNameMap();

        return $nameMap[$code] ?? $code;
    }

    protected function getDateOptions(): array
    {
        $start = Carbon::create(2026, 7, 10);
        $end = Carbon::today();

        $options = [];

        while ($start->lte($end)) {
            $options[$start->toDateString()] =
                $start->locale('ms')->translatedFormat('d/m/Y (l)');

            $start->addDay();
        }

        return array_reverse($options, true);
    }
}