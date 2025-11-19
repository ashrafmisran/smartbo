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
            // Get user statistics with state filtering
            $pendingQuery = User::where('status', 'pending');
            $verifiedQuery = User::where('status', 'verified');
            
            // Apply state filtering for non-superadmin users
            if (!auth()->user()?->is_superadmin) {
                $currentUserState = auth()->user()?->divisionKawasan?->negeri;
                
                if ($currentUserState) {
                    $pendingQuery->whereHas('divisionKawasan', function ($q) use ($currentUserState) {
                        $q->where('negeri', $currentUserState);
                    });
                    
                    $verifiedQuery->whereHas('divisionKawasan', function ($q) use ($currentUserState) {
                        $q->where('negeri', $currentUserState);
                    });
                } else {
                    // If user has no state, show 0 counts
                    $pendingQuery->whereRaw('1 = 0');
                    $verifiedQuery->whereRaw('1 = 0');
                }
            }
            
            $adminStats = [
                Stat::make('Pengguna Menunggu Pengesahan', 
                    $pendingQuery->count())
                    ->description('Bilangan pengguna menunggu kelulusan admin.')
                    ->descriptionIcon('heroicon-o-clock')
                    ->color('warning'),
                
                Stat::make('Pengguna Disahkan', 
                    $verifiedQuery->count())
                    ->description('Bilangan pengguna yang telah disahkan.')
                    ->descriptionIcon('heroicon-o-check-circle')
                    ->color('success'),
            ];
            
            $stats = array_merge($stats, $adminStats);

            
        }

        return $stats;
    }
}
