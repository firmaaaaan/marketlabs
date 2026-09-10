<?php

namespace Database\Seeders;

use App\Models\BenchFeeLevel;
use Illuminate\Database\Seeder;

class BenchFeeLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['name' => 'SLTP', 'label' => 'SLTP', 'sort_order' => 0],
            ['name' => 'SLTA', 'label' => 'SLTA', 'sort_order' => 1],
            ['name' => 'S1', 'label' => 'S1', 'sort_order' => 2],
            ['name' => 'S2/S3', 'label' => 'S2 / S3', 'sort_order' => 3],
        ];

        foreach ($levels as $level) {
            BenchFeeLevel::updateOrCreate(
                ['name' => $level['name']],
                $level
            );
        }
    }
}
