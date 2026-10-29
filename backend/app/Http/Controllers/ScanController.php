<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\TiketParkir;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ScanController extends Controller
{
    public function scan(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $member = Member::where(
            'qr_code',
            $request->qr_code
        )->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code member tidak ditemukan.',
            ], 404);
        }

        if (
            $member->expired_at &&
            now()->toDateString() > $member->expired_at->toDateString()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Masa berlaku member sudah habis.',
                'data' => [
                    'member' => $member,
                ],
            ], 403);
        }

        $sesiAktif = TiketParkir::where('member_id', $member->id)
            ->where('status', 'masuk')
            ->whereNull('waktu_keluar')
            ->first();

        if ($sesiAktif) {
            return response()->json([
                'success' => false,
                'message' => 'Member masih memiliki sesi parkir aktif. Silakan keluar terlebih dahulu sebelum masuk kembali.',
                'data' => [
                    'member' => $member,
                    'tiket' => $sesiAktif,
                ],
            ], 422);
        }

        $kodeTiket = TiketParkir::pluck('kode_tiket');
        $nomorTerbesar = 0;

        foreach ($kodeTiket as $kodeLama) {
            if (preg_match('/^A(\d+)$/', $kodeLama, $matches)) {
                $nomor = (int) $matches[1];
                if ($nomor > $nomorTerbesar) {
                    $nomorTerbesar = $nomor;
                }
            }
        }

        $nomor = $nomorTerbesar + 1;

        do {
            $kode = 'A' . str_pad($nomor, 2, '0', STR_PAD_LEFT);
            $nomor++;
        } while (TiketParkir::where('kode_tiket', $kode)->exists());

        $tiket = TiketParkir::create([
            'member_id' => $member->id,
            'kode_tiket' => $kode,
            'qr_code' => Str::random(60),
            'jenis' => 'member',
            'status' => 'masuk',
            'waktu_masuk' => now(),
            'waktu_keluar' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Member berhasil masuk.',
            'data' => [
                'member' => $member,
                'tiket' => $tiket,
            ],
        ], 201);
    }
}
