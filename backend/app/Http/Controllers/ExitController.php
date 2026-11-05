<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\TiketParkir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExitController extends Controller
{
    protected int $tarifPerJam = 3000;

    /**
     * =========================
     * SCAN QR (MEMBER / TIKET)
     * =========================
     */
    public function scan(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $input = trim($request->qr_code);

        // 1. Cek apakah ini QR kartu member atau kode member
        $member = Member::where('qr_code', $input)
            ->orWhere('kode', $input)
            ->first();

        if ($member) {
            $tiket = TiketParkir::where('member_id', $member->id)
                ->where('status', 'masuk')
                ->latest('waktu_masuk')
                ->first();

            if (!$tiket) {
                // Cek apakah member baru saja menyelesaikan sesi keluar sebelumnya
                $lastSesi = TiketParkir::where('member_id', $member->id)
                    ->latest('waktu_keluar')
                    ->first();

                if ($lastSesi && $lastSesi->status === 'keluar') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Member ini sudah menyelesaikan transaksi keluar sebelumnya dan tidak memiliki sesi parkir aktif di dalam area.',
                    ], 422);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Member ini tidak memiliki sesi parkir aktif di dalam area.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'type' => 'member',
                'message' => 'Member ditemukan.',
                'data' => [
                    'member_id' => $member->id,
                    'kode' => $member->kode,
                    'nama' => $member->nama,
                    'perusahaan' => $member->perusahaan,
                    'status_parkir' => $tiket->status,
                    'tiket_id' => $tiket->id,
                    'waktu_masuk' => $tiket->waktu_masuk,
                ],
            ]);
        }

        // 2. Kalau bukan member, cek sebagai tiket non-member di database
        $tiket = TiketParkir::where('qr_code', $input)
            ->orWhere('kode_tiket', $input)
            ->first();

        if (!$tiket) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak ditemukan atau kode tidak terdaftar dalam database sistem.',
            ], 404);
        }

        // Validasi tiket hangus / sudah pernah keluar
        if ($tiket->status === 'keluar') {
            return response()->json([
                'success' => false,
                'message' => 'Tiket sudah digunakan! Transaksi pembayaran tiket ini telah selesai dan tiket sudah hangus/tidak berlaku.',
            ], 422);
        }

        if ($tiket->status !== 'masuk') {
            return response()->json([
                'success' => false,
                'message' => 'Status tiket tidak valid (' . $tiket->status . ').',
            ], 422);
        }

        $durasiMenit = max(1, (int) round(Carbon::parse($tiket->waktu_masuk)->diffInMinutes(now())));
        $durasiJam = max(1, (int) ceil($durasiMenit / 60));
        $totalBayar = $durasiJam * $this->tarifPerJam;

        return response()->json([
            'success' => true,
            'type' => 'ticket',
            'message' => 'Tiket valid ditemukan di database sistem.',
            'data' => [
                'id' => $tiket->id,
                'kode_tiket' => $tiket->kode_tiket,
                'qr_code' => $tiket->qr_code,
                'status' => $tiket->status,
                'waktu_masuk' => $tiket->waktu_masuk,
                'durasi_menit' => $durasiMenit,
                'durasi_jam' => $durasiJam,
                'tarif_per_jam' => $this->tarifPerJam,
                'total_bayar' => $totalBayar,
            ],
        ]);
    }

    /**
     * =========================
     * KENDARAAN KELUAR - MEMBER
     * =========================
     */
    public function member(Request $request)
    {
        $request->validate([
            'member_id' => 'required|integer|exists:members,id',
            'tiket_id' => 'required|integer|exists:tiket_parkir,id',
            'plat_nomor' => 'required|string|max:20',
            'jenis_kendaraan' => 'required|in:motor,mobil',
        ]);

        return DB::transaction(function () use ($request) {
            $tiket = TiketParkir::where('id', $request->tiket_id)
                ->where('member_id', $request->member_id)
                ->lockForUpdate()
                ->first();

            if (!$tiket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tiket member tidak ditemukan.',
                ], 404);
            }

            if ($tiket->status !== 'masuk') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tiket sudah digunakan! Transaksi pembayaran tiket ini telah selesai dan tiket sudah hangus/tidak berlaku.',
                ], 422);
            }

            $tiket->update([
                'status' => 'keluar',
                'waktu_keluar' => now(),
                'nomor_polisi' => strtoupper($request->plat_nomor),
                'jenis_kendaraan' => $request->jenis_kendaraan,
                'total_bayar' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Member berhasil keluar. Pintu terbuka.',
            ]);
        });
    }

    /**
     * =========================
     * BAYAR TIKET NON MEMBER
     * =========================
     */
    public function bayar(Request $request)
    {
        $request->validate([
            'tiket_id' => 'required|integer|exists:tiket_parkir,id',
            'plat_nomor' => 'required|string|max:20',
            'jenis_kendaraan' => 'required|in:motor,mobil',
        ]);

        return DB::transaction(function () use ($request) {
            $tiket = TiketParkir::where('id', $request->tiket_id)
                ->lockForUpdate()
                ->first();

            if (!$tiket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tiket tidak ditemukan.',
                ], 404);
            }

            if ($tiket->status !== 'masuk') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tiket sudah digunakan! Transaksi pembayaran tiket ini telah selesai dan tiket sudah hangus/tidak berlaku.',
                ], 422);
            }

            $durasiMenit = max(1, (int) round(Carbon::parse($tiket->waktu_masuk)->diffInMinutes(now())));
            $durasiJam = max(1, (int) ceil($durasiMenit / 60));
            $totalBayar = $durasiJam * $this->tarifPerJam;

            $tiket->update([
                'status' => 'keluar',
                'waktu_keluar' => now(),
                'nomor_polisi' => strtoupper($request->plat_nomor),
                'jenis_kendaraan' => $request->jenis_kendaraan,
                'total_bayar' => $totalBayar,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil. Pintu terbuka.',
                'data' => [
                    'total_bayar' => $totalBayar,
                ],
            ]);
        });
    }
}
