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

        $kodeTerakhir = Member::where('kode', 'like', 'MBR-%')
            ->get()
            ->map(function ($member) {
                return (int) str_replace('MBR-', '', $member->kode);
            })
            ->max() ?? 0;

        $kodeBaru = 'MBR-' . str_pad(
            $kodeTerakhir + 1,
            3,
            '0',
            STR_PAD_LEFT
        );

        $member = Member::create([
            'kode' => $kodeBaru,
            'qr_code' => Str::random(60),
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
}
