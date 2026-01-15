<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use App\Models\IplPayment;

class FinancialBreakdownChart extends ChartWidget
{
    protected static ?string $heading = 'Distribusi Dana IPL';
    protected static ?int $sort = 2;
    // protected int | string | array $columnSpan = 'half'; // Adjust as needed

    protected function getData(): array
    {
        $data = \App\Models\IplPaymentAllocation::select('fund_name', DB::raw('SUM(amount) as total'))
            ->whereHas('iplPayment', fn($q) => $q->where('status', 'paid'))
            ->groupBy('fund_name')
            ->get();

        $labels = $data->pluck('fund_name')->toArray();
        $values = $data->pluck('total')->toArray();

        // Generate colors dynamically or use a fixed palette
        $colors = [
            '#10b981', '#f59e0b', '#3b82f6', '#ef4444', '#8b5cf6', '#ec4899'
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Distribusi Dana',
                    'data' => $values,
                    'backgroundColor' => array_slice($colors, 0, count($values)),
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
