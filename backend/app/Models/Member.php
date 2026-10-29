<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'qr_code',
        'nama',
        'perusahaan',
        'harga',
        'status',
        'expired_at',
    ];

    protected $casts = [
        'expired_at' => 'date',
        'harga' => 'decimal:2',
    ];

    public function tagihan()
    {
        return $this->hasMany(TagihanMember::class);
    }

    public function tiket()
    {
        return $this->hasMany(TiketParkir::class);
    }
}
