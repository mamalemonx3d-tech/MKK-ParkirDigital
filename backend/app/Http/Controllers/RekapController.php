<?php

namespace App\Http\Controllers;

use App\Models\TiketParkir;
use App\Models\TagihanMember;
use App\Models\User;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RekapController extends Controller
{
    /**
     * Menghitung total pendapatan:
     * 1. Tiket non-member (berdasarkan total_bayar)
     * 2. Tagihan member yang sudah lunas
     */
    private function hitungPendapatan($start, $end)
    {
        $pendapatanNonMember = TiketParkir::whereBetween('waktu_keluar', [$start, $end])
            ->where('status', 'keluar')
            ->where('jenis', 'non_member')
            ->whereNull('member_id')
            ->sum('total_bayar');

        $pendapatanMember = TagihanMember::whereBetween('tanggal_bayar', [
            $start->toDateString(),
            $end->toDateString()
        ])
            ->where('status', 'lunas')
            ->sum('jumlah');

        return (float) ($pendapatanNonMember + $pendapatanMember);
    }

    /**
     * Daftar tiket per periode.
     */
    private function daftarTiket($start, $end)
    {
        return TiketParkir::where(function ($query) use ($start, $end) {
            $query->whereBetween('waktu_masuk', [$start, $end])
                ->orWhere(function ($q) use ($start, $end) {
                    $q->whereBetween('waktu_keluar', [$start, $end])
                        ->where('status', 'keluar');
                });
        })
            ->orderBy('waktu_masuk', 'desc')
            ->get()
            ->map(function ($t) {
                $durasi = $t->waktu_keluar
                    ? Carbon::parse($t->waktu_masuk)->diffInMinutes(Carbon::parse($t->waktu_keluar))
                    : null;

                $jam = $durasi !== null ? max(1, (int) ceil($durasi / 60)) : 0;

                $biaya = (float) $t->total_bayar;
                if ($biaya == 0 && $t->jenis === 'non_member' && $t->status === 'keluar') {
                    $biaya = $jam * 3000;
                }

                return [
                    'id' => $t->id,
                    'kode_tiket' => $t->kode_tiket,
                    'nomor_polisi' => $t->nomor_polisi,
                    'jenis_kendaraan' => $t->jenis_kendaraan,
                    'waktu_masuk' => $t->waktu_masuk,
                    'waktu_keluar' => $t->waktu_keluar,
                    'status' => $t->status,
                    'jenis' => $t->jenis,
                    'member_id' => $t->member_id,
                    'durasi_menit' => $durasi,
                    'jam' => $jam,
                    'biaya' => $biaya,
                    'total_pendapatan' => $biaya,
                ];
            });
    }

    public function harian(Request $request)
    {
        $date = $request->get('date', Carbon::today()->toDateString());

        $start = Carbon::parse($date)->startOfDay();
        $end = Carbon::parse($date)->endOfDay();

        $masuk = TiketParkir::whereBetween('waktu_masuk', [$start, $end])->count();
        $keluar = TiketParkir::whereBetween('waktu_keluar', [$start, $end])
            ->where('status', 'keluar')
            ->count();

        $pendapatan = $this->hitungPendapatan($start, $end);
        $detail = $this->daftarTiket($start, $end);

        return response()->json([
            'success' => true,
            'data' => [
                'tanggal' => $date,
                'total_masuk' => $masuk,
                'total_keluar' => $keluar,
                'total_pendapatan' => $pendapatan,
                'detail' => $detail,
            ],
        ]);
    }

    public function mingguan(Request $request)
    {
        $date = Carbon::parse($request->get('date', Carbon::today()));

        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek = $date->copy()->endOfWeek();

        $results = [];
        $current = $startOfWeek->copy();

        while ($current->lte($endOfWeek)) {
            $dayStart = $current->copy()->startOfDay();
            $dayEnd = $current->copy()->endOfDay();

            $masuk = TiketParkir::whereBetween('waktu_masuk', [$dayStart, $dayEnd])->count();
            $keluar = TiketParkir::whereBetween('waktu_keluar', [$dayStart, $dayEnd])
                ->where('status', 'keluar')
                ->count();

            $pendapatan = $this->hitungPendapatan($dayStart, $dayEnd);

            $results[] = [
                'tanggal' => $current->toDateString(),
                'nama_hari' => $current->translatedFormat('l'),
                'total_masuk' => $masuk,
                'total_keluar' => $keluar,
                'total_pendapatan' => $pendapatan,
            ];

            $current->addDay();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'periode' => $startOfWeek->toDateString() . ' s/d ' . $endOfWeek->toDateString(),
                'ringkasan' => [
                    'total_masuk' => collect($results)->sum('total_masuk'),
                    'total_keluar' => collect($results)->sum('total_keluar'),
                    'total_pendapatan' => collect($results)->sum('total_pendapatan'),
                ],
                'detail' => $results,
                'tiket' => $this->daftarTiket(
                    $startOfWeek->copy()->startOfDay(),
                    $endOfWeek->copy()->endOfDay()
                ),
            ],
        ]);
    }

    public function bulanan(Request $request)
    {
        $date = Carbon::parse($request->get('date', Carbon::today()));

        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $results = [];
        $current = $startOfMonth->copy();

        while ($current->lte($endOfMonth)) {
            $dayStart = $current->copy()->startOfDay();
            $dayEnd = $current->copy()->endOfDay();

            $masuk = TiketParkir::whereBetween('waktu_masuk', [$dayStart, $dayEnd])->count();
            $keluar = TiketParkir::whereBetween('waktu_keluar', [$dayStart, $dayEnd])
                ->where('status', 'keluar')
                ->count();

            $pendapatan = $this->hitungPendapatan($dayStart, $dayEnd);

            $results[] = [
                'tanggal' => $current->toDateString(),
                'total_masuk' => $masuk,
                'total_keluar' => $keluar,
                'total_pendapatan' => $pendapatan,
            ];

            $current->addDay();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'periode' => $startOfMonth->format('F Y'),
                'ringkasan' => [
                    'total_masuk' => collect($results)->sum('total_masuk'),
                    'total_keluar' => collect($results)->sum('total_keluar'),
                    'total_pendapatan' => collect($results)->sum('total_pendapatan'),
                ],
                'detail' => $results,
                'tiket' => $this->daftarTiket(
                    $startOfMonth->copy()->startOfDay(),
                    $endOfMonth->copy()->endOfDay()
                ),
            ],
        ]);
    }

    public function summary()
    {
        $today = Carbon::today();

        $start = $today->copy()->startOfDay();
        $end = $today->copy()->endOfDay();

        $totalTiketAktif = TiketParkir::where('status', 'masuk')->count();
        $totalTiketHariIni = TiketParkir::whereBetween('waktu_masuk', [$start, $end])->count();
        $pendapatanHariIni = $this->hitungPendapatan($start, $end);
        $totalPetugas = User::where('role', 'petugas')->count();
        $totalMember = Member::count();

        return response()->json([
            'success' => true,
            'data' => [
                'tiket_aktif' => $totalTiketAktif,
                'tiket_hari_ini' => $totalTiketHariIni,
                'pendapatan_hari_ini' => $pendapatanHariIni,
                'total_petugas' => $totalPetugas,
                'total_member' => $totalMember,
            ],
        ]);
    }
}
