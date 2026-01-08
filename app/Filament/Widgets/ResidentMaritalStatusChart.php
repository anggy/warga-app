<?php

namespace App\Filament\Widgets;

use App\Models\Resident;
use Filament\Widgets\ChartWidget;

class ResidentMaritalStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Status Perkawinan';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = Resident::selectRaw('marital_status, count(*) as count')
            ->whereNotNull('marital_status')
            ->groupBy('marital_status')
            ->pluck('count', 'marital_status')
            ->toArray();

        $labels = array_map(function($status) {
            return match ($status) {
                'single' => 'Belum Kawin',
                'married' => 'Kawin',
                'divorced' => 'Cerai Hidup',
                'widowed' => 'Cerai Mati',
                default => $status,
            };
        }, array_keys($data));

        return [
            'datasets' => [
                [
                    'label' => 'Status Perkawinan',
                    'data' => array_values($data),
                    'backgroundColor' => [
                        '#3b82f6',
                        '#10b981', 
                        '#f59e0b',
                        '#ef4444',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
