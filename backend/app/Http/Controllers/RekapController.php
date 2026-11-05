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
        $pendapatanNonMember = (float) TiketParkir::whereBetween('waktu_keluar', [$start, $end])
            ->where('status', 'keluar')
            ->where('jenis', 'non_member')
            ->sum('total_bayar');

        $pendapatanMember = (float) TagihanMember::whereBetween('tanggal_bayar', [
            $start->toDateString(),
            $end->toDateString()
        ])
            ->where('status', 'lunas')
            ->sum('jumlah');

        return [
            'non_member' => $pendapatanNonMember,
            'member' => $pendapatanMember,
            'total' => $pendapatanNonMember + $pendapatanMember,
        ];
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
                    ? max(1, (int) round(Carbon::parse($t->waktu_masuk)->diffInMinutes(Carbon::parse($t->waktu_keluar))))
                    : max(1, (int) round(Carbon::parse($t->waktu_masuk)->diffInMinutes(Carbon::now())));

                // Pembulatan ke atas (ceil) jam penuh: < 1 jam -> 1 jam; 1 jam 30 menit -> 2 jam
                $jam = max(1, (int) ceil($durasi / 60));

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
                    'petugas' => $t->petugas_nama ?: ($t->status === 'keluar' ? 'Petugas 1' : 'Kiosk Mandiri'),
                    'status' => $t->status,
                    'jenis' => $t->jenis,
                    'member_id' => $t->member_id,
                    'durasi_menit' => $durasi,
                    'jam' => $jam,
                    'durasi_jam' => $jam,
                    'durasi_teks' => "{$jam} Jam",
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
        $masukNonMember = TiketParkir::whereBetween('waktu_masuk', [$start, $end])->where('jenis', 'non_member')->count();
        $masukMember = TiketParkir::whereBetween('waktu_masuk', [$start, $end])->where('jenis', 'member')->count();

        $keluar = TiketParkir::whereBetween('waktu_keluar', [$start, $end])
            ->where('status', 'keluar')
            ->count();
        $keluarNonMember = TiketParkir::whereBetween('waktu_keluar', [$start, $end])
            ->where('status', 'keluar')
            ->where('jenis', 'non_member')
            ->count();
        $keluarMember = TiketParkir::whereBetween('waktu_keluar', [$start, $end])
            ->where('status', 'keluar')
            ->where('jenis', 'member')
            ->count();

        $pendapatan = $this->hitungPendapatan($start, $end);
        $detail = $this->daftarTiket($start, $end);

        return response()->json([
            'success' => true,
            'data' => [
                'tanggal' => $date,
                'total_masuk' => $masuk,
                'total_keluar' => $keluar,
                'masuk_non_member' => $masukNonMember,
                'masuk_member' => $masukMember,
                'keluar_non_member' => $keluarNonMember,
                'keluar_member' => $keluarMember,
                'pendapatan_non_member' => $pendapatan['non_member'],
                'pendapatan_member' => $pendapatan['member'],
                'total_pendapatan' => $pendapatan['total'],
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
                'pendapatan_non_member' => $pendapatan['non_member'],
                'pendapatan_member' => $pendapatan['member'],
                'total_pendapatan' => $pendapatan['total'],
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
                    'pendapatan_non_member' => collect($results)->sum('pendapatan_non_member'),
                    'pendapatan_member' => collect($results)->sum('pendapatan_member'),
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
                'pendapatan_non_member' => $pendapatan['non_member'],
                'pendapatan_member' => $pendapatan['member'],
                'total_pendapatan' => $pendapatan['total'],
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
                    'pendapatan_non_member' => collect($results)->sum('pendapatan_non_member'),
                    'pendapatan_member' => collect($results)->sum('pendapatan_member'),
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
        $totalKeluarHariIni = TiketParkir::whereBetween('waktu_keluar', [$start, $end])->where('status', 'keluar')->count();
        $totalSudahKeluar = TiketParkir::where('status', 'keluar')->count();
        $pendapatan = $this->hitungPendapatan($start, $end);
        $totalPetugas = User::where('role', 'petugas')->count();
        $totalMember = Member::count();
        $totalMemberLunas = Member::where('status', 'lunas')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'tiket_aktif' => $totalTiketAktif,
                'sedang_parkir' => $totalTiketAktif,
                'total_belum_keluar' => $totalTiketAktif,
                'tiket_hari_ini' => $totalTiketHariIni,
                'masuk_hari_ini' => $totalTiketHariIni,
                'keluar_hari_ini' => $totalKeluarHariIni,
                'total_sudah_keluar' => $totalSudahKeluar,
                'pendapatan_hari_ini' => $pendapatan['total'],
                'pendapatan_non_member' => $pendapatan['non_member'],
                'pendapatan_member' => $pendapatan['member'],
                'total_petugas' => $totalPetugas,
                'total_member' => $totalMember,
                'total_member_lunas' => $totalMemberLunas,
            ],
        ]);
    }

    public function petugas(Request $request)
    {
        $today = Carbon::today();
        $start = $today->copy()->startOfDay();
        $end = $today->copy()->endOfDay();

        $totalTiketHariIni = TiketParkir::whereBetween('waktu_masuk', [$start, $end])->count();
        $pendapatan = $this->hitungPendapatan($start, $end);

        $petugasList = User::where('role', 'petugas')
            ->orderBy('name')
            ->get()
            ->map(function ($u, $idx) use ($totalTiketHariIni, $pendapatan) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'role' => 'Petugas Kasir & Gerbang',
                    'status' => 'Aktif Bertugas',
                    'shift' => ($idx % 2 === 0) ? 'Shift Pagi (07:00 - 15:00)' : 'Shift Siang (15:00 - 23:00)',
                    'total_tiket_shift' => $totalTiketHariIni,
                    'total_koleksi_shift' => $pendapatan['total'],
                    'terdaftar_pada' => $u->created_at ? $u->created_at->format('d M Y') : '-',
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $petugasList,
        ]);
    }

    public function chart(Request $request)
    {
        $dateStr = $request->get('date', Carbon::today()->toDateString());
        $start = Carbon::parse($dateStr)->startOfDay();
        $end = Carbon::parse($dateStr)->endOfDay();

        // 1. Ambil seluruh tiket pada tanggal ini
        $allTiket = TiketParkir::where(function ($q) use ($start, $end) {
            $q->whereBetween('waktu_masuk', [$start, $end])
                ->orWhere(function ($sub) use ($start, $end) {
                    $sub->whereBetween('waktu_keluar', [$start, $end])
                        ->where('status', 'keluar');
                });
        })->get();

        // 2. Tagihan member pada tanggal ini
        $tagihanMember = TagihanMember::where('status', 'lunas')
            ->whereBetween('tanggal_bayar', [$start->toDateString(), $end->toDateString()])
            ->sum('jumlah');

        $hourly = [];
        $maxActivity = 0;
        $peakHourLabel = '-';

        // 24 jam: 00:00 s.d 23:00
        for ($h = 0; $h < 24; $h++) {
            $hStart = $start->copy()->hour($h)->minute(0)->second(0);
            $hEnd = $start->copy()->hour($h)->minute(59)->second(59);

            $masukCount = $allTiket->filter(function ($t) use ($hStart, $hEnd) {
                return $t->waktu_masuk && Carbon::parse($t->waktu_masuk)->between($hStart, $hEnd);
            })->count();

            $keluarCount = $allTiket->filter(function ($t) use ($hStart, $hEnd) {
                return $t->waktu_keluar && $t->status === 'keluar' && Carbon::parse($t->waktu_keluar)->between($hStart, $hEnd);
            })->count();

            $pendapatanTiket = (float) $allTiket->filter(function ($t) use ($hStart, $hEnd) {
                return $t->waktu_keluar && $t->status === 'keluar' && $t->jenis === 'non_member' && Carbon::parse($t->waktu_keluar)->between($hStart, $hEnd);
            })->sum('total_bayar');

            $totalActivity = $masukCount + $keluarCount;
            if ($totalActivity > $maxActivity) {
                $maxActivity = $totalActivity;
                $peakHourLabel = sprintf('%02d:00 - %02d:00', $h, ($h + 1) % 24);
            }

            $hourly[] = [
                'hour' => sprintf('%02d:00', $h),
                'hour_num' => $h,
                'masuk' => $masukCount,
                'keluar' => $keluarCount,
                'total_kendaraan' => $totalActivity,
                'pendapatan' => $pendapatanTiket,
            ];
        }

        $totalMasuk = $allTiket->filter(fn ($t) => $t->waktu_masuk && Carbon::parse($t->waktu_masuk)->between($start, $end))->count();
        $totalKeluar = $allTiket->filter(fn ($t) => $t->waktu_keluar && $t->status === 'keluar' && Carbon::parse($t->waktu_keluar)->between($start, $end))->count();
        $totalPendapatanNonMember = (float) $allTiket->filter(fn ($t) => $t->status === 'keluar' && $t->jenis === 'non_member' && $t->waktu_keluar && Carbon::parse($t->waktu_keluar)->between($start, $end))->sum('total_bayar');
        $totalPendapatanSemua = $totalPendapatanNonMember + (float) $tagihanMember;

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $dateStr,
                'total_masuk' => $totalMasuk,
                'total_keluar' => $totalKeluar,
                'total_pendapatan' => $totalPendapatanSemua,
                'pendapatan_non_member' => $totalPendapatanNonMember,
                'pendapatan_member' => (float) $tagihanMember,
                'peak_hour' => $peakHourLabel,
                'hourly' => $hourly,
            ],
        ]);
    }
}
