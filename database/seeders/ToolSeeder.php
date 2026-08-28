<?php

namespace Database\Seeders;

use App\Models\Tool;
use App\Models\ToolCategory;
use Illuminate\Database\Seeder;

class ToolSeeder extends Seeder
{
    public function run(): void
    {
        $tools = [
            ['code' => 'AL-001', 'name' => 'Mikroskop Binokuler', 'category' => 'Optik', 'brand' => 'Olympus', 'series' => 'CX23', 'type' => 'internal', 'total_stock' => 5, 'price_per_day' => 75000, 'description' => 'Mikroskop dengan dua lensa okuler, perbesaran hingga 1000x, ideal untuk pengamatan preparat biologi.'],
            ['code' => 'AL-002', 'name' => 'Autoclave Sterilisasi', 'category' => 'Sterilisasi', 'brand' => 'Hirayama', 'series' => 'HVE-50', 'type' => 'internal', 'total_stock' => 3, 'price_per_day' => 150000, 'description' => 'Alat sterilisasi uap bertekanan tinggi, kapasitas 50 liter, untuk sterilisasi alat dan media.'],
            ['code' => 'AL-003', 'name' => 'Sentrifuge Laboratorium', 'category' => 'Sentrifugasi', 'brand' => 'Eppendorf', 'series' => '5804', 'type' => 'internal', 'total_stock' => 4, 'price_per_day' => 120000, 'description' => 'Sentrifuge meja dengan 12 tabung, kecepatan hingga 6000 rpm, untuk pemisahan sampel.'],
            ['code' => 'AL-004', 'name' => 'Spektrofotometer UV-Vis', 'category' => 'Analitik', 'brand' => 'Shimadzu', 'series' => 'UV-1900', 'type' => 'internal', 'total_stock' => 2, 'price_per_day' => 200000, 'description' => 'Spektrofotometer untuk pengukuran absorbansi sampel pada rentang UV dan Visible.'],
            ['code' => 'AL-005', 'name' => 'pH Meter Digital', 'category' => 'Pengukuran', 'brand' => 'Hanna', 'series' => 'HI98107', 'type' => 'internal', 'total_stock' => 8, 'price_per_day' => 25000, 'description' => 'pH meter digital portabel dengan akurasi tinggi, kalibrasi otomatis 1-3 titik.'],
            ['code' => 'AL-006', 'name' => 'Inkubator Mikrobiologi', 'category' => 'Kultur', 'brand' => 'Memmert', 'series' => 'IN55', 'type' => 'internal', 'total_stock' => 3, 'price_per_day' => 100000, 'description' => 'Inkubator dengan kontrol suhu 0-70°C, untuk pertumbuhan kultur mikroorganisme.'],
            ['code' => 'AL-007', 'name' => 'Hot Plate Magnetic Stirrer', 'category' => 'Pemanasan', 'brand' => 'IKA', 'series' => 'C-MAG HS7', 'type' => 'internal', 'total_stock' => 6, 'price_per_day' => 50000, 'description' => 'Hot plate dengan pengaduk magnetik, suhu hingga 340°C, untuk pemanasan dan pengadukan larutan.'],
            ['code' => 'AL-008', 'name' => 'Timbangan Analitik', 'category' => 'Pengukuran', 'brand' => 'Ohaus', 'series' => 'PA224', 'type' => 'internal', 'total_stock' => 4, 'price_per_day' => 60000, 'description' => 'Timbangan analitik presisi 0,1 mg, dengan ruang anti-hembusan angin.'],
            ['code' => 'AL-009', 'name' => 'Laminar Air Flow', 'category' => 'Sterilisasi', 'brand' => 'Esco', 'series' => 'LHC-4A1', 'type' => 'internal', 'total_stock' => 2, 'price_per_day' => 175000, 'description' => 'Meja kerja steril dengan aliran udara laminar, untuk penanganan sampel bebas kontaminasi.'],
            ['code' => 'AL-010', 'name' => 'Water Bath', 'category' => 'Pemanasan', 'brand' => 'Memmert', 'series' => 'WNB14', 'type' => 'internal', 'total_stock' => 5, 'price_per_day' => 45000, 'description' => 'Penangas air digital dengan kontrol suhu 0-100°C, untuk inkubasi sampel.'],
            ['code' => 'AL-011', 'name' => 'PCR Thermal Cycler', 'category' => 'Biologi Molekuler', 'brand' => 'Bio-Rad', 'series' => 'T100', 'type' => 'external', 'total_stock' => 1, 'price_per_day' => 250000, 'description' => 'Mesin PCR dengan blok 96 well, untuk amplifikasi DNA dalam penelitian molekuler.'],
            ['code' => 'AL-012', 'name' => 'Refrigerator Laboratorium', 'category' => 'Penyimpanan', 'brand' => 'Panasonic', 'series' => 'MPR-514', 'type' => 'internal', 'total_stock' => 3, 'price_per_day' => 80000, 'description' => 'Kulkas khusus laboratorium, rentang suhu 2-8°C, untuk penyimpanan reagen dan sampel.'],
        ];

        foreach ($tools as $tool) {
            $category = ToolCategory::where('name', $tool['category'])->first();

            $existing = Tool::where('code', $tool['code'])->first();

            if ($existing) {
                // Hanya perbarui data alat tanpa mereset stok yang sedang dipinjam.
                $existing->update([
                    'name' => $tool['name'],
                    'category_id' => $category?->id,
                    'brand' => $tool['brand'],
                    'series' => $tool['series'],
                    'description' => $tool['description'],
                    'price_per_day' => $tool['price_per_day'],
                    'is_active' => true,
                ]);

                continue;
            }

            Tool::create([
                'code' => $tool['code'],
                'name' => $tool['name'],
                'category_id' => $category?->id,
                'brand' => $tool['brand'],
                'series' => $tool['series'],
                'description' => $tool['description'],
                'total_stock' => $tool['total_stock'],
                'available_stock' => $tool['total_stock'],
                'price_per_day' => $tool['price_per_day'],
                'is_active' => true,
            ]);
        }
    }
}
