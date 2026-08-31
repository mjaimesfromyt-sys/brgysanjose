<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@barangay.com'],
            [
                'first_name'    => 'RJ',
                'last_name'     => 'Boniel',
                'password'      => Hash::make('adminrjboniel'),
                'role'          => 'admin',
                'status'        => 'active',
                'resident_type' => 'resident',
                'verified_at'   => now(),
            ]
        );
    }
}