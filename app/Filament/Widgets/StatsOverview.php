<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $stats = [
            Stat::make('Total Warga', \App\Models\Resident::count())
                ->description('Total warga terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
        ];

        // Dynamic Funds Stats
        $funds = \App\Models\Fund::where('is_active', true)->get();
        
        foreach ($funds as $fund) {
            $total = \App\Models\IplPaymentAllocation::where('fund_name', $fund->name)
                ->whereHas('iplPayment', fn($q) => $q->where('status', 'paid'))
                ->sum('amount');

            $stats[] = Stat::make("Saldo {$fund->name}", 'Rp ' . number_format($total, 0, ',', '.'))
                ->description("Total Dana {$fund->name}")
                ->descriptionIcon('heroicon-m-banknotes') // Generic icon, or add field to Fund model
                ->color('success');
        }

        return $stats;
    }
}
