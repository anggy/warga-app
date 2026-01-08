<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Warga', \App\Models\Resident::count())
                ->description('Total warga terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('Total Keluarga', \App\Models\Resident::where('is_head_of_family', true)->count())
                ->description('Kepala Keluarga')
                ->descriptionIcon('heroicon-m-home-modern')
                ->color('success'),
            Stat::make('Total Rumah', \App\Models\House::count())
                ->description('Total unit rumah')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('warning'),
            Stat::make('Warga Sementara', \App\Models\Resident::whereIn('status', ['contract', 'periodic'])->count())
                ->description('Kontrak & Periodik')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),
        ];
    }
}
