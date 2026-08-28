<?php

namespace Database\Seeders;

use App\Models\ToolCategory;
use Illuminate\Database\Seeder;

class ToolCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Optik',
            'Sterilisasi',
            'Sentrifugasi',
            'Analitik',
            'Pengukuran',
            'Kultur',
            'Pemanasan',
            'Biologi Molekuler',
            'Penyimpanan',
        ];

        foreach ($categories as $name) {
            ToolCategory::firstOrCreate(['name' => $name]);
        }
    }
}
