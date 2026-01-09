<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $settings = \App\Models\SystemSetting::first();
    $houses = \App\Models\House::all(['block', 'number', 'status', 'latitude', 'longitude']);
    
    // Financial Data
    $totalIncome = \App\Models\IplPayment::where('status', 'paid')->sum('amount');
    $totalExpense = \App\Models\Expense::sum('amount');
    $balance = $totalIncome - $totalExpense;

    // Default values if no settings exist
    $data = [
        'app_name' => $settings->app_name ?? 'Warga App',
        'latitude' => $settings->map_latitude ?? -6.200000,
        'longitude' => $settings->map_longitude ?? 106.816666,
        'zoom' => $settings->map_zoom ?? 13,
        'houses' => $houses,
        'totalIncome' => $totalIncome,
        'totalExpense' => $totalExpense,
        'balance' => $balance,
    ];
    return view('landing', $data);
});
