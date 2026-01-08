<?php

namespace App\Filament\Widgets;

use App\Models\Resident;
use Filament\Widgets\ChartWidget;

class ResidentStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Status Tempat Tinggal';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Resident::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Translate labels if needed, or use the raw values
        // 'permanent' => 'Tetap', 'contract' => 'Kontrak', 'periodic' => 'Periodik'
        
        $labels = array_map(function($status) {
            return match ($status) {
                'permanent' => 'Tetap',
                'contract' => 'Kontrak',
                'periodic' => 'Periodik',
                default => $status,
            };
        }, array_keys($data));

        return [
            'datasets' => [
                [
                    'label' => 'Status Warga',
                    'data' => array_values($data),
                    'backgroundColor' => [
                        '#10b981', // success
                        '#f59e0b', // warning
                        '#3b82f6', // info
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
