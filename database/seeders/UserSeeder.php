<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'email' => 'superadmin@marketlabs.id',
                'nim_nip' => 'SUP-0001',
                'role' => User::ROLE_SUPERADMIN,
            ],
            [
                'name' => 'Admin MarketLabs',
                'username' => 'admin',
                'email' => 'admin@marketlabs.id',
                'nim_nip' => 'ADM-0001',
                'role' => User::ROLE_ADMIN,
            ],
            [
                'name' => 'Staf Laboratorium',
                'username' => 'laboran',
                'email' => 'staf@marketlabs.id',
                'nim_nip' => 'STF-0001',
                'role' => User::ROLE_LABORAN,
            ],
            [
                'name' => 'Peneliti Contoh',
                'username' => 'peneliti',
                'email' => 'peneliti@marketlabs.id',
                'nim_nip' => 'PNL-0001',
                'role' => User::ROLE_USER,
            ],
            [
                'name' => 'Test User',
                'username' => 'testuser',
                'email' => 'test@example.com',
                'nim_nip' => 'TST-0001',
                'role' => User::ROLE_USER,
            ],
            [
                'name' => 'Client Contoh',
                'username' => 'client',
                'email' => 'client@marketlabs.id',
                'nim_nip' => 'CLT-0001',
                'role' => User::ROLE_USER,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'username' => $user['username'],
                    'nim_nip' => $user['nim_nip'],
                    'role' => $user['role'],
                    'participant_code' => User::generateParticipantCode(),
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ]
            );
        }
    }
}
