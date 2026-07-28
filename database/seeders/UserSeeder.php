<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Owner Account
        $owner = User::firstOrCreate(
            ['email' => 'owner@smartkasir.com'],
            [
                'name' => 'Pemilik Toko (Owner)',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $owner->assignRole('owner');

        // Admin Account
        $admin = User::firstOrCreate(
            ['email' => 'admin@smartkasir.com'],
            [
                'name' => 'Admin Toko',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $admin->assignRole('admin');

        // Kasir Account
        $kasir = User::firstOrCreate(
            ['email' => 'kasir@smartkasir.com'],
            [
                'name' => 'Budi (Kasir)',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $kasir->assignRole('kasir');
    }
}
