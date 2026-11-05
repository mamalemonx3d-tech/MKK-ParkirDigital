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

        $member = Member::where('qr_code', $request->qr_code)
            ->orWhere('kode', $request->qr_code)
            ->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Kartu atau QR Code member tidak ditemukan dalam sistem.',
            ], 404);
        }

        if ($member->status === 'expired' || ($member->expired_at && now()->toDateString() > $member->expired_at->toDateString())) {
            return response()->json([
                'success' => false,
                'message' => 'Masa berlaku kartu member sudah habis (kadaluarsa / expired). Harap lakukan perpanjangan langganan.',
                'data' => [
                    'member' => $member,
                ],
            ], 403);
        }

        if ($member->status !== 'lunas') {
            return response()->json([
                'success' => false,
                'message' => 'Status keanggotaan belum lunas. Harap lakukan pembayaran iuran terlebih dahulu.',
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

        // Generate kode tiket unik yang aman dan tidak dapat dipalsukan
        do {
            $kode = 'MBR-TKT-' . date('ymd') . '-' . strtoupper(Str::random(6));
        } while (TiketParkir::where('kode_tiket', $kode)->exists());

        $tiket = TiketParkir::create([
            'member_id' => $member->id,
            'kode_tiket' => $kode,
            'qr_code' => 'QR-' . strtoupper(Str::random(24)),
            'jenis' => 'member',
            'status' => 'masuk',
            'waktu_masuk' => now(),
            'waktu_keluar' => null,
            'total_bayar' => 0,
            'petugas_nama' => 'Kiosk Mandiri',
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
