<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's users.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@marketlabs.id',
                'nim_nip' => 'SUP-0001',
                'role' => User::ROLE_SUPERADMIN,
            ],
            [
                'name' => 'Admin MarketLabs',
                'email' => 'admin@marketlabs.id',
                'nim_nip' => 'ADM-0001',
                'role' => User::ROLE_ADMIN,
            ],
            [
                'name' => 'Staf Laboratorium',
                'email' => 'staf@marketlabs.id',
                'nim_nip' => 'STF-0001',
                'role' => User::ROLE_LABORAN,
            ],
            [
                'name' => 'Peneliti Contoh',
                'email' => 'peneliti@marketlabs.id',
                'nim_nip' => 'PNL-0001',
                'role' => User::ROLE_USER,
            ],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'nim_nip' => 'TST-0001',
                'role' => User::ROLE_USER,
            ],
            [
                'name' => 'Client Contoh',
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
