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
        'total_terbayar',
        'status',
        'expired_at',
    ];

    protected $casts = [
        'expired_at' => 'date',
        'harga' => 'decimal:2',
        'total_terbayar' => 'decimal:2',
    ];

    protected $appends = [
        'computed_status',
    ];

    public function getComputedStatusAttribute()
    {
        if ($this->expired_at && now()->toDateString() > $this->expired_at->toDateString()) {
            return 'expired';
        }
        return $this->status;
    }

    public function tagihan()
    {
        return $this->hasMany(TagihanMember::class);
    }

    public function tiket()
    {
        return $this->hasMany(TiketParkir::class);
    }
}
