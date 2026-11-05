<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\TagihanMember;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MemberController extends Controller
{
    public function index()
    {
        $members = Member::latest()->get()->map(function ($m) {
            $isDateExpired = $m->expired_at && now()->toDateString() > Carbon::parse($m->expired_at)->toDateString();
            $effectiveStatus = $isDateExpired ? 'expired' : $m->status;

            return [
                'id' => $m->id,
                'kode' => $m->kode,
                'qr_code' => $m->qr_code,
                'nama' => $m->nama,
                'perusahaan' => $m->perusahaan,
                'harga' => (float) $m->harga,
                'total_terbayar' => (float) ($m->total_terbayar ?? 0),
                'status' => $m->status,
                'computed_status' => $effectiveStatus,
                'expired_at' => $m->expired_at ? Carbon::parse($m->expired_at)->toDateString() : null,
                'created_at' => $m->created_at,
                'updated_at' => $m->updated_at,
            ];
        });

        return response()->json($members);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'perusahaan' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
            'durasi_bulan' => 'nullable|integer|min:1',
            'total_terbayar' => 'nullable|numeric|min:0',
            'status' => 'required|in:lunas,belum,expired',
            'expired_at' => 'required|date',
        ]);

        $hargaPerBulan = (float) $request->harga;
        $durasiBulan = (int) ($request->durasi_bulan ?: 1);

        if ($request->has('total_terbayar') && $request->total_terbayar !== null && $request->total_terbayar !== '') {
            $totalTerbayar = (float) $request->total_terbayar;
        } else {
            $totalTerbayar = ($request->status === 'lunas') ? ($hargaPerBulan * $durasiBulan) : 0;
        }

        // Generate kode member unik kriptografis
        do {
            $kodeBaru = 'MBR-' . date('y') . strtoupper(Str::random(6));
        } while (Member::where('kode', $kodeBaru)->exists());

        $qrCode = 'MBR-QR-' . strtoupper(Str::random(24));

        $member = Member::create([
            'kode' => $kodeBaru,
            'qr_code' => $qrCode,
            'nama' => $request->nama,
            'perusahaan' => $request->perusahaan,
            'harga' => $hargaPerBulan,
            'total_terbayar' => $totalTerbayar,
            'status' => $request->status,
            'expired_at' => $request->expired_at,
        ]);

        TagihanMember::create([
            'member_id' => $member->id,
            'jumlah' => $totalTerbayar > 0 ? $totalTerbayar : $hargaPerBulan,
            'status' => $request->status === 'lunas' ? 'lunas' : 'belum',
            'tanggal_tagihan' => now()->toDateString(),
            'tanggal_bayar' => $request->status === 'lunas' ? now()->toDateString() : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Member berhasil dibuat',
            'data' => $member,
        ], 201);
    }

    public function show($id)
    {
        $member = Member::with(['tagihan', 'tiket'])->findOrFail($id);

        $isDateExpired = $member->expired_at && now()->toDateString() > Carbon::parse($member->expired_at)->toDateString();
        $member->computed_status = $isDateExpired ? 'expired' : $member->status;

        return response()->json($member);
    }

    public function update(Request $request, $id)
    {
        $member = Member::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'perusahaan' => 'required|string|max:255',
            'harga' => 'nullable|numeric|min:0',
            'total_terbayar' => 'nullable|numeric|min:0',
            'status' => 'required|in:lunas,belum,expired',
            'expired_at' => 'required|date',
        ]);

        $data = [
            'nama' => $request->nama,
            'perusahaan' => $request->perusahaan,
            'status' => $request->status,
            'expired_at' => $request->expired_at,
        ];

        if ($request->has('harga') && $request->harga !== null) {
            $data['harga'] = (float) $request->harga;
        }

        if ($request->has('total_terbayar') && $request->total_terbayar !== null) {
            $data['total_terbayar'] = (float) $request->total_terbayar;
        }

        if ($request->filled('kode') && $request->kode !== $member->kode) {
            $request->validate([
                'kode' => 'unique:members,kode,' . $id,
            ]);
            $data['kode'] = $request->kode;
        }

        $member->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Data kartu member berhasil diperbarui',
            'data' => $member,
        ]);
    }

    public function lunas($id)
    {
        $member = Member::findOrFail($id);

        $totalTerbayar = (float) ($member->total_terbayar > 0 ? $member->total_terbayar : $member->harga);

        $member->update([
            'status' => 'lunas',
            'total_terbayar' => $totalTerbayar,
        ]);

        // Update status tagihan terkait
        TagihanMember::where('member_id', $member->id)
            ->where('status', 'belum')
            ->update([
                'status' => 'lunas',
                'tanggal_bayar' => now()->toDateString(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Status member berhasil diubah menjadi lunas',
            'data' => $member,
        ]);
    }

    public function destroy($id)
    {
        $member = Member::findOrFail($id);

        $member->delete();

        return response()->json([
            'success' => true,
            'message' => 'Member berhasil dihapus',
        ]);
    }

    public function checkStatus(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
        ]);

        $query = trim($request->input('query'));

        $member = Member::where('kode', $query)
            ->orWhere('qr_code', $query)
            ->orWhere('nama', 'like', "%{$query}%")
            ->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Data kartu member tidak ditemukan. Periksa kembali kode atau QR Anda.',
            ], 404);
        }

        $activeTicket = \App\Models\TiketParkir::where('member_id', $member->id)
            ->where('status', 'masuk')
            ->latest('waktu_masuk')
            ->first();

        $expired = Carbon::parse($member->expired_at);
        $isExpired = ($member->status === 'expired') || ($expired->isPast() && !$expired->isToday());
        $tipeKendaraan = ((float) $member->harga >= 100000) ? 'Mobil (Roda 4)' : 'Motor (Roda 2)';

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $member->id,
                'kode' => $member->kode,
                'nama' => $member->nama,
                'perusahaan' => $member->perusahaan,
                'harga' => $member->harga,
                'total_terbayar' => $member->total_terbayar,
                'status_iuran' => $isExpired ? 'expired' : $member->status,
                'expired_at' => $member->expired_at ? Carbon::parse($member->expired_at)->toDateString() : null,
                'is_expired' => $isExpired,
                'tipe_kendaraan' => $tipeKendaraan,
                'sedang_parkir' => (bool) $activeTicket,
                'waktu_masuk' => $activeTicket ? $activeTicket->waktu_masuk : null,
                'kode_tiket_aktif' => $activeTicket ? $activeTicket->kode_tiket : null,
            ],
        ]);
    }
}
