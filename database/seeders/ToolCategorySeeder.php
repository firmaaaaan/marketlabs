<?php

namespace Database\Seeders;

use App\Models\ToolCategory;
use Illuminate\Database\Seeder;

class ToolCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Kategori alat kesehatan
        $healthCategories = [
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

        foreach ($healthCategories as $name) {
            ToolCategory::firstOrCreate(['name' => $name]);
        }

        // Kategori alat non-kesehatan
        $nonHealthCategories = [
            'Elektronika',
            'Mekanik',
            'Field & Outdoor',
            'Kantor & Administrasi',
            'Komputer & IT',
            'Pertanian & Lingkungan',
        ];

        foreach ($nonHealthCategories as $name) {
            ToolCategory::firstOrCreate(['name' => $name]);
        }
    }
}
