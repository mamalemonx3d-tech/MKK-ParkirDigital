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

        User::updateOrCreate(
            ['email' => 'mamalemonx3d@gmail.com'],
            [
                'name' => 'Fajri',
                'password' => Hash::make('password'),
                'role' => 'petugas',
            ]
        );

        // 2. Data Member Resmi Sesuai Data Pengguna (Fixed Tarif: Rp 150.000 / Bulan)
        // Member 1: Abhista Bhanu Hermawan - PT RUAS
        $member1 = Member::updateOrCreate(
            ['nama' => 'Abhista Bhanu Hermawan'],
            [
                'kode' => 'MBR-26001',
                'qr_code' => 'MBR-QR-SAMPLE01',
                'nama' => 'Abhista Bhanu Hermawan',
                'perusahaan' => 'PT RUAS',
                'harga' => 150000, // Harga 1 Bulan
                'total_terbayar' => 300000, // Bayar langsung 2 bulan
                'status' => 'lunas',
                'expired_at' => now()->addMonths(2)->toDateString(),
            ]
        );

        TagihanMember::updateOrCreate(
            ['member_id' => $member1->id, 'tanggal_tagihan' => now()->toDateString()],
            [
                'jumlah' => 300000,
                'status' => 'lunas',
                'tanggal_bayar' => now()->toDateString(),
            ]
        );

        // Member 2: Radekha Yona Pratama - PT Traspac
        $member2 = Member::updateOrCreate(
            ['nama' => 'Radekha Yona Pratama'],
            [
                'kode' => 'MBR-26002',
                'qr_code' => 'MBR-QR-SAMPLE02',
                'nama' => 'Radekha Yona Pratama',
                'perusahaan' => 'PT Traspac',
                'harga' => 150000,
                'total_terbayar' => 150000,
                'status' => 'lunas',
                'expired_at' => now()->addMonths(1)->toDateString(),
            ]
        );

        TagihanMember::updateOrCreate(
            ['member_id' => $member2->id, 'tanggal_tagihan' => now()->toDateString()],
            [
                'jumlah' => 150000,
                'status' => 'lunas',
                'tanggal_bayar' => now()->toDateString(),
            ]
        );

        // Member 3: Aria Renaldi - RSKD Pasar Belantong
        $member3 = Member::updateOrCreate(
            ['nama' => 'Aria Renaldi'],
            [
                'kode' => 'MBR-26003',
                'qr_code' => 'MBR-QR-SAMPLE03',
                'nama' => 'Aria Renaldi',
                'perusahaan' => 'RSKD Pasar Belantong',
                'harga' => 150000,
                'total_terbayar' => 150000,
                'status' => 'lunas',
                'expired_at' => now()->addMonths(1)->toDateString(),
            ]
        );

        TagihanMember::updateOrCreate(
            ['member_id' => $member3->id, 'tanggal_tagihan' => now()->toDateString()],
            [
                'jumlah' => 150000,
                'status' => 'lunas',
                'tanggal_bayar' => now()->toDateString(),
            ]
        );

        // Member 4: Rakha Al-Rifad - PT Equity
        $member4 = Member::updateOrCreate(
            ['nama' => 'Rakha Al-Rifad'],
            [
                'kode' => 'MBR-26EUDYXJ',
                'qr_code' => 'MBR-QR-SAMPLE04',
                'nama' => 'Rakha Al-Rifad',
                'perusahaan' => 'PT Equity',
                'harga' => 150000,
                'total_terbayar' => 150000,
                'status' => 'lunas',
                'expired_at' => now()->addMonths(1)->toDateString(),
            ]
        );

        TagihanMember::updateOrCreate(
            ['member_id' => $member4->id, 'tanggal_tagihan' => now()->toDateString()],
            [
                'jumlah' => 150000,
                'status' => 'lunas',
                'tanggal_bayar' => now()->toDateString(),
            ]
        );
    }
}
