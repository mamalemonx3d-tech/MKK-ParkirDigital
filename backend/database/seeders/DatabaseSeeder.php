<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Member;
use App\Models\TagihanMember;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Pengguna Sistem (Admin & Petugas)
        User::updateOrCreate(
            ['email' => 'admin@parkir.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'petugas@parkir.com'],
            [
                'name' => 'Petugas 1',
                'password' => Hash::make('password'),
                'role' => 'petugas',
            ]
        );

        // 2. Seed Data Contoh Member
        $member1 = Member::updateOrCreate(
            ['kode' => 'MBR-26001'],
            [
                'qr_code' => 'MBR-QR-SAMPLE01',
                'nama' => 'Abhista Bhanu Hermawan',
                'perusahaan' => 'PT RUAS DIGITAL',
                'harga' => 150000,
                'status' => 'lunas',
                'expired_at' => now()->addMonths(1)->toDateString(),
            ]
        );

        TagihanMember::firstOrCreate(
            ['member_id' => $member1->id, 'tanggal_tagihan' => now()->toDateString()],
            [
                'jumlah' => $member1->harga,
                'status' => 'lunas',
                'tanggal_bayar' => now()->toDateString(),
            ]
        );

        $member2 = Member::updateOrCreate(
            ['kode' => 'MBR-26002'],
            [
                'qr_code' => 'MBR-QR-SAMPLE02',
                'nama' => 'Budi Santoso',
                'perusahaan' => 'PT Maju Bersama',
                'harga' => 50000,
                'status' => 'lunas',
                'expired_at' => now()->addMonths(1)->toDateString(),
            ]
        );

        TagihanMember::firstOrCreate(
            ['member_id' => $member2->id, 'tanggal_tagihan' => now()->toDateString()],
            [
                'jumlah' => $member2->harga,
                'status' => 'lunas',
                'tanggal_bayar' => now()->toDateString(),
            ]
        );
    }
}
