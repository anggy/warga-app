<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FundsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $funds = [
            [
                'name' => 'Security',
                'description' => 'Biaya pengamanan lingkungan',
                'default_amount' => 70000,
                'is_active' => true,
            ],
            [
                'name' => 'Pemeliharaan',
                'description' => 'Biaya pemeliharaan lingkungan dan fasilitas umum',
                'default_amount' => 30000,
                'is_active' => true,
            ],
            [
                'name' => 'Kas Warga',
                'description' => 'Dana kas untuk kegiatan warga',
                'default_amount' => 30000,
                'is_active' => true,
            ],
        ];

        foreach ($funds as $fund) {
            \App\Models\Fund::create($fund);
        }
    }
}
