<?php

namespace Database\Seeders;

use App\Models\Mitra;
use Illuminate\Database\Seeder;

class MitraSeeder extends Seeder
{
    public function run(): void
    {
        $mitras = [
            [
                'name' => 'Universitas Gadjah Mada',
                'logo' => 'https://ui-avatars.com/api/?name=UGM&background=166534&color=fff&size=128&bold=true',
                'website' => 'https://ugm.ac.id',
                'is_active' => true,
                'sort_order' => 0,
            ],
            [
                'name' => 'Institut Teknologi Bandung',
                'logo' => 'https://ui-avatars.com/api/?name=ITB&background=1e40af&color=fff&size=128&bold=true',
                'website' => 'https://itb.ac.id',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Universitas Indonesia',
                'logo' => 'https://ui-avatars.com/api/?name=UI&background=7c3aed&color=fff&size=128&bold=true',
                'website' => 'https://ui.ac.id',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'BPOM RI',
                'logo' => 'https://ui-avatars.com/api/?name=BPOM&background=dc2626&color=fff&size=128&bold=true',
                'website' => 'https://bpom.go.id',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'LIPI',
                'logo' => 'https://ui-avatars.com/api/?name=LIPI&background=0369a1&color=fff&size=128&bold=true',
                'website' => 'https://lipi.go.id',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Universitas Diponegoro',
                'logo' => 'https://ui-avatars.com/api/?name=UNDIP&background=b45309&color=fff&size=128&bold=true',
                'website' => 'https://undip.ac.id',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($mitras as $mitra) {
            Mitra::updateOrCreate(
                ['name' => $mitra['name']],
                $mitra
            );
        }
    }
}
