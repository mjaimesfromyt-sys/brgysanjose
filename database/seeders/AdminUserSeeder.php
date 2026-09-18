<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin — Punong Barangay
        User::updateOrCreate(
            ['email' => 'admin@barangay.com'],
            [
                'first_name'    => 'Josefina',
                'last_name'     => 'Gurrea',
                'password'      => Hash::make('admin@sanjose#1'),
                'role'          => 'super_admin',
                'position'      => 'Punong Barangay',
                'status'        => 'active',
                'resident_type' => 'resident',
                'verified_at'   => now(),
            ]
        );

        // Admin — Barangay Secretary
        User::updateOrCreate(
            ['email' => 'adminSec@barangay.com'],
            [
                'first_name'  => 'Hanna Joy',
                'last_name'   => 'Credo',
                'password'    => Hash::make('admin@sanjose#1'),
                'role'        => 'admin',
                'position'    => 'Barangay Secretary',
                'status'      => 'active',
                'verified_at' => now(),
            ]
        );
    }
}
