<?php

namespace Database\Seeders;

use App\Models\SampleForm;
use App\Models\SampleType;
use App\Models\SampleUnit;
use App\Models\TestParameter;
use Illuminate\Database\Seeder;

class SampleTestSeeder extends Seeder
{
    public function run(): void
    {
        $forms = ['Cair', 'Padat', 'Bubuk', 'Semi Padat', 'Gas'];

        foreach ($forms as $form) {
            SampleForm::firstOrCreate(['name' => $form], ['is_active' => true]);
        }

        $types = ['Air', 'Air Sungai', 'Urine', 'Serum Darah', 'Darah', 'Susu', 'Tanah', 'Kain', 'Makanan', 'Minuman', 'Tumbuhan', 'Logam'];

        foreach ($types as $type) {
            SampleType::firstOrCreate(['name' => $type], ['is_active' => true]);
        }

        $units = [
            ['name' => 'Sampel', 'symbol' => 'smpl'],
            ['name' => 'Running', 'symbol' => 'running'],
            ['name' => '500 ml', 'symbol' => '500ml'],
            ['name' => '4 Sampel', 'symbol' => '4smpl'],
        ];

        foreach ($units as $unit) {
            SampleUnit::updateOrCreate(['name' => $unit['name']], ['symbol' => $unit['symbol'], 'is_active' => true]);
        }

        $parameters = [
            ['name' => 'Pengujian dengan Hematology Analyzer', 'method' => null, 'unit' => 'Sampel', 'rate' => 50000, 'description' => 'Analisis komponen darah lengkap menggunakan alat hematology analyzer otomatis.'],
            ['name' => 'Isolasi DNA / RNA', 'method' => null, 'unit' => 'Running', 'rate' => 176000, 'description' => 'Ekstraksi dan pemurnian materi genetik dari sampel biologis.'],
            ['name' => 'Pengujian PCR', 'method' => 'PCR', 'unit' => 'Running', 'rate' => 194000, 'description' => 'Amplifikasi sekuens DNA untuk deteksi gen target.'],
            ['name' => 'Pengujian Elektroforesis', 'method' => 'Elektroforesis Gel', 'unit' => 'Running', 'rate' => 165000, 'description' => 'Pemisahan fragmen DNA atau protein berdasarkan ukuran.'],
            ['name' => 'Pengujian SDS-Page', 'method' => 'SDS-PAGE', 'unit' => 'Running', 'rate' => 312000, 'description' => 'Analisis profil protein dengan pemisahan elektroforesis denaturasi.'],
            ['name' => 'Pembuatan Media', 'method' => null, 'unit' => '500 ml', 'rate' => 170000, 'description' => 'Penyiapan media pertumbuhan mikroorganisme sesuai kebutuhan.'],
            ['name' => 'Pengujian Kadar Air Manual', 'method' => 'Oven Gravimetri', 'unit' => 'Sampel', 'rate' => 50000, 'description' => 'Pengukuran kadar air sampel dengan metode oven konvensional.'],
            ['name' => 'Pengujian Kadar Air Moisture Balance', 'method' => 'Moisture Balance', 'unit' => 'Sampel', 'rate' => 10000, 'description' => 'Pengukuran kadar air cepat menggunakan alat moisture balance.'],
            ['name' => 'Pengujian Glukosa dalam Serum Darah', 'method' => 'Enzimatik Kolorimetri', 'unit' => '4 Sampel', 'rate' => 170000, 'description' => 'Penentuan kadar glukosa darah dalam sampel serum.'],
            ['name' => 'Pengujian Kolesterol dalam Serum Darah', 'method' => 'Enzimatik Kolorimetri', 'unit' => '4 Sampel', 'rate' => 200000, 'description' => 'Penentuan kadar kolesterol total dalam sampel serum.'],
            ['name' => 'Identifikasi Karbohidrat dalam Urin', 'method' => 'Uji Kualitatif', 'unit' => 'Sampel', 'rate' => 43000, 'description' => 'Deteksi keberadaan karbohidrat dalam sampel urin.'],
            ['name' => 'Identifikasi Protein dalam Susu', 'method' => 'Uji Biuret', 'unit' => 'Sampel', 'rate' => 42000, 'description' => 'Deteksi keberadaan protein dalam sampel susu.'],
            ['name' => 'Pengujian Karbohidrat dengan Metode Nelson Somogy', 'method' => 'Nelson Somogy', 'unit' => 'Sampel', 'rate' => 99000, 'description' => 'Penentuan kadar gula pereduksi dengan metode Nelson Somogy.'],
            ['name' => 'Pengujian Protein dengan Metode Lowry Folling', 'method' => 'Lowry Folling', 'unit' => 'Sampel', 'rate' => 77000, 'description' => 'Penentuan kadar protein total dengan metode Lowry Folling.'],
        ];

        foreach ($parameters as $parameter) {
            $unit = SampleUnit::where('name', $parameter['unit'])->first();

            if (! $unit) {
                continue;
            }

            TestParameter::updateOrCreate(
                ['name' => $parameter['name']],
                [
                    'method' => $parameter['method'],
                    'unit_id' => $unit->id,
                    'rate' => $parameter['rate'],
                    'description' => $parameter['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
