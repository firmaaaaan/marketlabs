<?php

namespace Database\Seeders;

use App\Models\Tool;
use App\Models\ToolCategory;
use Illuminate\Database\Seeder;

class NonHealthToolSeeder extends Seeder
{
    public function run(): void
    {
        $tools = [
            ['code' => 'NK-001', 'name' => 'Multimeter Digital', 'category' => 'Elektronika', 'brand' => 'Fluke', 'series' => '87V', 'total_stock' => 4, 'price_per_day' => 35000, 'description' => 'Multimeter digital industri untuk pengukuran tegangan, arus, dan resistansi dengan akurasi tinggi.'],
            ['code' => 'NK-002', 'name' => 'Oscilloscope', 'category' => 'Elektronika', 'brand' => 'Keysight', 'series' => 'DSOX1202A', 'total_stock' => 2, 'price_per_day' => 80000, 'description' => 'Oscilloscope digital 2-channel, bandwidth 70 MHz, untuk analisis sinyal elektronik.'],
            ['code' => 'NK-003', 'name' => 'Power Supply Regulated', 'category' => 'Elektronika', 'brand' => 'GW Instek', 'series' => 'GPS-3303', 'total_stock' => 3, 'price_per_day' => 25000, 'description' => 'Catu daya terkontrol 0-30V, 0-3A, dual output untuk kebutuhan laboratorium elektronik.'],
            ['code' => 'NK-004', 'name' => 'Solder Station', 'category' => 'Elektronika', 'brand' => 'Hakko', 'series' => 'FX-888D', 'total_stock' => 6, 'price_per_day' => 15000, 'description' => 'Stasiun solder suhu terkontrol 200-480°C, untuk perakitan dan perbaikan komponen.'],
            ['code' => 'NK-005', 'name' => 'Drill Machine', 'category' => 'Mekanik', 'brand' => 'Bosch', 'series' => 'GSR 120-LI', 'total_stock' => 5, 'price_per_day' => 30000, 'description' => 'Mesin bor baterai 12V, 2 kecepatan, untuk pengerjaan logam dan kayu.'],
            ['code' => 'NK-006', 'name' => 'Grinder Mesin', 'category' => 'Mekanik', 'brand' => 'Makita', 'series' => 'GA5030', 'total_stock' => 3, 'price_per_day' => 40000, 'description' => 'Mesin gerinda tangan 4 inch, 11000 RPM, untuk pemotongan dan pengamplasan logam.'],
            ['code' => 'NK-007', 'name' => 'Torque Wrench', 'category' => 'Mekanik', 'brand' => 'Tekiro', 'series' => 'TR-311', 'total_stock' => 4, 'price_per_day' => 20000, 'description' => 'Kunci torsi 10-110 Nm, untuk pemasangan baut dengan torsi terkontrol.'],
            ['code' => 'NK-008', 'name' => 'GPS Handheld', 'category' => 'Field & Outdoor', 'brand' => 'Garmin', 'series' => 'eTrex 32x', 'total_stock' => 3, 'price_per_day' => 45000, 'description' => 'GPS handheld tahan air, untuk navigasi dan pencarian koordinat lapangan.'],
            ['code' => 'NK-009', 'name' => 'Water Quality Meter', 'category' => 'Field & Outdoor', 'brand' => 'YSI', 'series' => 'ProDSS', 'total_stock' => 2, 'price_per_day' => 120000, 'description' => 'Pengukur kualitas air multi-parameter, untuk pengukuran pH, DO, TDS di lapangan.'],
            ['code' => 'NK-010', 'name' => 'Soil Sampling Kit', 'category' => 'Field & Outdoor', 'brand' => 'Eijkelkamp', 'series' => '10.03', 'total_stock' => 4, 'price_per_day' => 25000, 'description' => 'Peralatan pengambilan sampel tanah lengkap, untuk analisis fisik dan kimia tanah.'],
            ['code' => 'NK-011', 'name' => 'Printer Laser Multifungsi', 'category' => 'Kantor & Administrasi', 'brand' => 'HP', 'series' => 'MFP M428fdn', 'total_stock' => 3, 'price_per_day' => 20000, 'description' => 'Printer laser multifungsi, cetak/fotokopi/scan/fax, untuk kebutuhan administrasi.'],
            ['code' => 'NK-012', 'name' => 'Shredder Dokumen', 'category' => 'Kantor & Administrasi', 'brand' => 'Koris', 'series' => 'K-21', 'total_stock' => 2, 'price_per_day' => 15000, 'description' => 'Mesin penghancur kertas level P-4, untuk pengamanan dokumen rahasia.'],
            ['code' => 'NK-013', 'name' => 'Proyektor Presentasi', 'category' => 'Kantor & Administrasi', 'brand' => 'Epson', 'series' => 'EB-X51', 'total_stock' => 2, 'price_per_day' => 50000, 'description' => 'Proyektor XGA 3800 lumens, untuk presentasi dan rapat.'],
            ['code' => 'NK-014', 'name' => 'Laptop Kerja', 'category' => 'Komputer & IT', 'brand' => 'Lenovo', 'series' => 'ThinkPad T14', 'total_stock' => 5, 'price_per_day' => 75000, 'description' => 'Laptop bisnis i5 Gen 11, 16GB RAM, 512GB SSD, untuk kebutuhan komputasi.'],
            ['code' => 'NK-015', 'name' => 'NAS Storage', 'category' => 'Komputer & IT', 'brand' => 'Synology', 'series' => 'DS220+', 'total_stock' => 2, 'price_per_day' => 60000, 'description' => 'Network Attached Storage 2-bay, untuk penyimpanan dan backup data jaringan.'],
            ['code' => 'NK-016', 'name' => 'UPS Online', 'category' => 'Komputer & IT', 'brand' => 'APC', 'series' => 'SMT1500I', 'total_stock' => 3, 'price_per_day' => 35000, 'description' => 'UPS online 1500VA/1000W, proteksi listrik untuk peralatan komputer.'],
            ['code' => 'NK-017', 'name' => 'Traktor Mini', 'category' => 'Pertanian & Lingkungan', 'brand' => 'Kubota', 'series' => 'L5018', 'total_stock' => 1, 'price_per_day' => 200000, 'description' => 'Traktor tangan 50 HP, untuk pengolahan lahan pertanian skala kecil-menengah.'],
            ['code' => 'NK-018', 'name' => 'Soil Moisture Sensor', 'category' => 'Pertanian & Lingkungan', 'brand' => 'Decagon', 'series' => 'EC5', 'total_stock' => 6, 'price_per_day' => 30000, 'description' => 'Sensor kelembaban tanah volumetrik, untuk monitoring irigasi dan pertanian presisi.'],
            ['code' => 'NK-019', 'name' => 'Weather Station Portable', 'category' => 'Pertanian & Lingkungan', 'brand' => 'Davis', 'series' => 'Vantage Vue', 'total_stock' => 2, 'price_per_day' => 90000, 'description' => 'Stasiun cuaca portabel, mengukur suhu, kelembaban, angin, dan curah hujan.'],
        ];

        foreach ($tools as $tool) {
            $category = ToolCategory::where('name', $tool['category'])->first();

            $existing = Tool::where('code', $tool['code'])->first();

            if ($existing) {
                $existing->update([
                    'name' => $tool['name'],
                    'category_id' => $category?->id,
                    'brand' => $tool['brand'],
                    'series' => $tool['series'],
                    'description' => $tool['description'],
                    'price_per_day' => $tool['price_per_day'],
                    'type' => 'non-kesehatan',
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
                'type' => 'non-kesehatan',
                'is_active' => true,
            ]);
        }
    }
}
