<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@parkir.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'no_hp' => '081234567890',
            ]
        );

        User::updateOrCreate(
            ['email' => 'petugas@parkir.com'],
            [
                'name' => 'Petugas 1',
                'password' => Hash::make('password'),
                'role' => 'petugas',
                'no_hp' => '081234567891',
            ]
        );
    }
}
