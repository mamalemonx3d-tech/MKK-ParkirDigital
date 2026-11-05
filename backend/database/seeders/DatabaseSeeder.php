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

        // 2. Seed Data Contoh Member dengan berbagai status (Lunas, Belum Lunas, Expired)
        $member1 = Member::updateOrCreate(
            ['kode' => 'MBR-26001'],
            [
                'qr_code' => 'MBR-QR-SAMPLE01',
                'nama' => 'Abhista Bhanu Hermawan',
                'perusahaan' => 'PT RUAS DIGITAL',
                'harga' => 150000, // Harga 1 Bulan
                'total_terbayar' => 300000, // Bayar langsung 2 bulan
                'status' => 'lunas',
                'expired_at' => now()->addMonths(2)->toDateString(),
            ]
        );

        TagihanMember::firstOrCreate(
            ['member_id' => $member1->id, 'tanggal_tagihan' => now()->toDateString()],
            [
                'jumlah' => 300000,
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
                'harga' => 150000,
                'total_terbayar' => 150000,
                'status' => 'lunas',
                'expired_at' => now()->addMonths(1)->toDateString(),
            ]
        );

        TagihanMember::firstOrCreate(
            ['member_id' => $member2->id, 'tanggal_tagihan' => now()->toDateString()],
            [
                'jumlah' => 150000,
                'status' => 'lunas',
                'tanggal_bayar' => now()->toDateString(),
            ]
        );

        $member3 = Member::updateOrCreate(
            ['kode' => 'MBR-26003'],
            [
                'qr_code' => 'MBR-QR-SAMPLE03',
                'nama' => 'Siti Rahmawati',
                'perusahaan' => 'CV Karya Gemilang',
                'harga' => 150000,
                'total_terbayar' => 0,
                'status' => 'belum',
                'expired_at' => now()->addDays(14)->toDateString(),
            ]
        );

        TagihanMember::firstOrCreate(
            ['member_id' => $member3->id, 'tanggal_tagihan' => now()->toDateString()],
            [
                'jumlah' => 150000,
                'status' => 'belum',
                'tanggal_bayar' => null,
            ]
        );

        $member4 = Member::updateOrCreate(
            ['kode' => 'MBR-26004'],
            [
                'qr_code' => 'MBR-QR-SAMPLE04',
                'nama' => 'Hendra Wijaya',
                'perusahaan' => 'PT Cipta Kreasi',
                'harga' => 150000,
                'total_terbayar' => 150000,
                'status' => 'expired',
                'expired_at' => now()->subDays(5)->toDateString(),
            ]
        );
    }
}
