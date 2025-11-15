<?php

namespace App\Livewire;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\CallRecord;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Nombor telah dihubungi', CallRecord::where('kod_cula', '!=', null)->count())
                ->description('Jumlah panggilan yang telah direkodkan dengan keputusan cula.')
                ->descriptionIcon('heroicon-o-phone'),
        ];
    }
}
