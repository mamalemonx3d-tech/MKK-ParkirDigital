<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TiketParkir extends Model
{
    use HasFactory;

    protected $table = 'tiket_parkir';

    protected $fillable = [
        'member_id',
        'kode_tiket',
        'qr_code',
        'jenis',
        'nomor_polisi',
        'status',
        'waktu_masuk',
        'waktu_keluar',
        'total_bayar',
        'jenis_kendaraan',
    ];

    protected $casts = [
        'waktu_masuk' => 'datetime',
        'waktu_keluar' => 'datetime',
        'total_bayar' => 'float',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
