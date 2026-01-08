<?php

namespace App\Filament\Widgets;

use App\Models\IplPayment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class IplPaymentChart extends ChartWidget
{
    protected static ?string $heading = 'Pemasukan IPL (12 Bulan Terakhir)';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Get data for the last 12 months
        $data = IplPayment::select(
                DB::raw('DATE_FORMAT(paid_at, "%Y-%m") as month'),
                DB::raw('SUM(amount) as total')
            )
            ->where('paid_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Prepare labels and values
        $labels = [];
        $values = [];
        
        // Fill in missing months if needed, or just map existing data
        // For simplicity, we'll map the query results directly for now
        foreach ($data as $item) {
            $labels[] = Carbon::createFromFormat('Y-m', $item->month)->format('M Y');
            $values[] = $item->total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Pemasukan (Rp)',
                    'data' => $values,
                    'borderColor' => '#10b981', // Emerald equivalent
                    'fill' => 'start',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
