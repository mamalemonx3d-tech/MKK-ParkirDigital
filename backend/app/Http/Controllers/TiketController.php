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
        // Eksekusi super cepat & ringan (< 5ms)
        $last = TiketParkir::orderByDesc('id')->first();
        $lastNumber = $last ? (int) preg_replace('/\D+/', '', $last->kode_tiket) : 0;
        $nomor = $lastNumber + 1;
        $kode = 'A' . str_pad($nomor, 2, '0', STR_PAD_LEFT);
        $qrCode = Str::random(32);

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
            "message" => "Tiket berhasil dibuat",
            "data" => $tiket,
        ], 201);
    }

    public function masuk(Request $request)
    {
        $request->validate([
            "qr_code" => "required|string",
        ]);

        $tiket = TiketParkir::where("qr_code", $request->qr_code)->first();

        if (!$tiket) {
            return response()->json([
                "success" => false,
                "message" => "QR tidak terdaftar",
            ], 404);
        }

        if ($tiket->status === "masuk") {
            return response()->json([
                "success" => false,
                "message" => "Kendaraan dengan QR ini sudah masuk",
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
            "message" => "Kendaraan berhasil masuk",
            "data" => $tiket,
        ], 200);
    }

    public function cek($qrCode)
    {
        $tiket = TiketParkir::where("qr_code", $qrCode)
            ->where("status", "masuk")
            ->first();

        if (!$tiket) {
            return response()->json([
                "success" => false,
                "message" => "Tiket tidak ditemukan atau kendaraan sudah keluar.",
            ], 404);
        }

        $waktuMasuk = Carbon::parse($tiket->waktu_masuk);
        $durasiMenit = max(0, $waktuMasuk->diffInMinutes(Carbon::now()));
        $jam = max(1, (int) ceil($durasiMenit / 60));

        $tarifPerJam = 3000;
        $totalBayar = $jam * $tarifPerJam;

        return response()->json([
            "success" => true,
            "data" => [
                "id" => $tiket->id,
                "kode_tiket" => $tiket->kode_tiket,
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
