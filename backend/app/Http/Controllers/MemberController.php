<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\TagihanMember;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    public function index()
    {
        return response()->json(
            Member::latest()->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'perusahaan' => 'required',
            'harga' => 'required|numeric',
            'status' => 'required|in:lunas,belum',
            'expired_at' => 'required|date',
        ]);

        // Generate kode member unik kriptografis yang aman dan tidak dapat dipalsukan
        do {
            $kodeBaru = 'MBR-' . date('y') . strtoupper(Str::random(6));
        } while (Member::where('kode', $kodeBaru)->exists());

        $qrCode = 'MBR-QR-' . strtoupper(Str::random(24));

        $member = Member::create([
            'kode' => $kodeBaru,
            'qr_code' => $qrCode,
            'nama' => $request->nama,
            'perusahaan' => $request->perusahaan,
            'harga' => $request->harga,
            'status' => $request->status,
            'expired_at' => $request->expired_at,
        ]);

        TagihanMember::create([
            'member_id' => $member->id,
            'jumlah' => $member->harga,
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

        return response()->json($member);
    }

    public function update(Request $request, $id)
    {
        $member = Member::findOrFail($id);

        $request->validate([
            'kode' => 'required|unique:members,kode,' . $id,
            'nama' => 'required',
            'perusahaan' => 'required',
            'status' => 'required|in:lunas,belum',
            'expired_at' => 'required|date',
        ]);

        $member->update([
            'kode' => $request->kode,
            'nama' => $request->nama,
            'perusahaan' => $request->perusahaan,
            'status' => $request->status,
            'expired_at' => $request->expired_at,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Member berhasil diperbarui',
            'data' => $member,
        ]);
    }

    public function lunas($id)
    {
        $member = Member::findOrFail($id);

        $member->update([
            'status' => 'lunas',
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

        $expired = \Carbon\Carbon::parse($member->expired_at);
        $isExpired = $expired->isPast() && !$expired->isToday();
        $tipeKendaraan = ((float) $member->harga >= 100000) ? 'Mobil (Roda 4)' : 'Motor (Roda 2)';

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $member->id,
                'kode' => $member->kode,
                'nama' => $member->nama,
                'perusahaan' => $member->perusahaan,
                'harga' => $member->harga,
                'status_iuran' => $member->status,
                'expired_at' => $member->expired_at,
                'is_expired' => $isExpired,
                'tipe_kendaraan' => $tipeKendaraan,
                'sedang_parkir' => (bool) $activeTicket,
                'waktu_masuk' => $activeTicket ? $activeTicket->waktu_masuk : null,
                'kode_tiket_aktif' => $activeTicket ? $activeTicket->kode_tiket : null,
            ],
        ]);
    }
}
