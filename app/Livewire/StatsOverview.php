<?php

namespace App\Livewire;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\CallRecord;
use App\Models\User;

class StatsOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $stats = [
            Stat::make('Nombor telah dihubungi', 
                CallRecord::where('kod_cula', '!=', null)
                    ->where('user_id', auth()->id()) // Show owner-specific stats only
                    ->count())
                ->description('Jumlah panggilan yang telah direkodkan dengan keputusan cula.')
                ->descriptionIcon('heroicon-o-phone'),
        ];

        // Add admin-only user statistics
        if (auth()->user()?->is_admin) {
            $adminStats = [
                Stat::make('Pengguna Menunggu Pengesahan', 
                    User::where('status', 'pending')->count())
                    ->description('Bilangan pengguna menunggu kelulusan admin.')
                    ->descriptionIcon('heroicon-o-clock')
                    ->color('warning'),
                
                Stat::make('Pengguna Disahkan', 
                    User::where('status', 'verified')->count())
                    ->description('Bilangan pengguna yang telah disahkan.')
                    ->descriptionIcon('heroicon-o-check-circle')
                    ->color('success'),
            ];
            
            $stats = array_merge($stats, $adminStats);
        }

        return $stats;
    }
}
