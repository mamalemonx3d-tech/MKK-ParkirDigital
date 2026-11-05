<?php

namespace App\Http\Controllers;

use App\Models\TiketParkir;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TiketController extends Controller
{
    public function index()
    {
        $tiket = TiketParkir::with('member')->latest()->get();

        return response()->json($tiket);
    }

    public function create(Request $request)
    {
        // Generate kode unik kriptografis yang aman dan tidak dapat ditebak
        do {
            $kode = 'TKT-' . date('ymd') . '-' . strtoupper(Str::random(6));
        } while (TiketParkir::where('kode_tiket', $kode)->exists());

        $qrCode = 'QR-' . strtoupper(Str::random(24));

        $tiket = TiketParkir::create([
            "kode_tiket" => $kode,
            "qr_code" => $qrCode,
            "jenis" => "non_member",
            "status" => "masuk",
            "waktu_masuk" => now(),
            "waktu_keluar" => null,
            "total_bayar" => 0,
        ]);

        return response()->json([
            "success" => true,
            "message" => "Tiket berhasil dibuat dan tersimpan di database sistem.",
            "data" => $tiket,
        ], 201);
    }

    public function masuk(Request $request)
    {
        $request->validate([
            "qr_code" => "required|string",
        ]);

        $input = trim($request->qr_code);

        $tiket = TiketParkir::where("qr_code", $input)
            ->orWhere("kode_tiket", $input)
            ->first();

        if (!$tiket) {
            return response()->json([
                "success" => false,
                "message" => "Tiket tidak terdaftar dalam sistem.",
            ], 404);
        }

        if ($tiket->status === "keluar") {
            return response()->json([
                "success" => false,
                "message" => "Tiket sudah digunakan! Transaksi pembayaran tiket ini telah selesai dan tiket sudah hangus/tidak berlaku.",
            ], 422);
        }

        if ($tiket->status === "masuk") {
            return response()->json([
                "success" => false,
                "message" => "Kendaraan dengan tiket ini sudah berada di dalam area parkir.",
                "data" => $tiket,
            ], 400);
        }

        $tiket->update([
            "status" => "masuk",
            "waktu_masuk" => now(),
            "waktu_keluar" => null,
        ]);

        return response()->json([
            "success" => true,
            "message" => "Kendaraan berhasil masuk.",
            "data" => $tiket,
        ], 200);
    }

    public function cek($qrCode)
    {
        $input = trim($qrCode);

        $tiket = TiketParkir::where("qr_code", $input)
            ->orWhere("kode_tiket", $input)
            ->first();

        if (!$tiket) {
            return response()->json([
                "success" => false,
                "message" => "Tiket tidak ditemukan atau kode tidak valid dalam sistem.",
            ], 404);
        }

        if ($tiket->status === "keluar") {
            return response()->json([
                "success" => false,
                "message" => "Tiket sudah digunakan! Transaksi pembayaran tiket ini telah selesai dan tiket sudah hangus/tidak berlaku.",
            ], 422);
        }

        if ($tiket->status !== "masuk") {
            return response()->json([
                "success" => false,
                "message" => "Status tiket tidak valid (" . $tiket->status . ").",
            ], 422);
        }

        $waktuMasuk = Carbon::parse($tiket->waktu_masuk);
        $durasiMenit = max(1, (int) round($waktuMasuk->diffInMinutes(Carbon::now())));
        $jam = max(1, (int) ceil($durasiMenit / 60));

        $tarifPerJam = 3000;
        $totalBayar = $jam * $tarifPerJam;

        return response()->json([
            "success" => true,
            "data" => [
                "id" => $tiket->id,
                "kode_tiket" => $tiket->kode_tiket,
                "qr_code" => $tiket->qr_code,
                "jenis" => $tiket->jenis,
                "member" => $tiket->jenis === "member",
                "status" => $tiket->status,
                "waktu_masuk" => $tiket->waktu_masuk,
                "durasi_menit" => $durasiMenit,
                "jam" => $jam,
                "tarif_per_jam" => $tarifPerJam,
                "total_bayar" => $totalBayar,
            ],
        ], 200);
    }

    public function destroy($id)
    {
        $tiket = TiketParkir::find($id);
        if (!$tiket) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak ditemukan.',
            ], 404);
        }

        $tiket->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data tiket berhasil dihapus.',
        ]);
    }

    public function clear()
    {
        TiketParkir::truncate();

        return response()->json([
            'success' => true,
            'message' => 'Seluruh rekaman tiket parkir berhasil dikosongkan.',
        ]);
    }
}
