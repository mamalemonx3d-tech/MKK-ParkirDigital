<?php

namespace App\Http\Controllers;

use App\Models\TagihanMember;
use Illuminate\Http\Request;

class TagihanMemberController extends Controller
{
    public function index()
    {
        $tagihan = TagihanMember::with('member')
            ->latest('tanggal_tagihan')
            ->get();

        return response()->json($tagihan);
    }

    public function show(TagihanMember $tagihanMember)
    {
        $tagihanMember->load('member');

        return response()->json($tagihanMember);
    }

    public function bayar(TagihanMember $tagihanMember)
    {
        if ($tagihanMember->status === 'lunas') {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan ini sudah lunas.',
            ], 400);
        }

        $tagihanMember->update([
            'status' => 'lunas',
            'tanggal_bayar' => now()->toDateString(),
        ]);

        if ($tagihanMember->member) {
            $tagihanMember->member->update([
                'status' => 'lunas',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil.',
            'data' => $tagihanMember->load('member'),
        ]);
    }
}
