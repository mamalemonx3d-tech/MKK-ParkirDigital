<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TagihanMember extends Model
{
    use HasFactory;

    protected $table = 'tagihan_members';

    protected $fillable = [
        'member_id',
        'jumlah',
        'status',
        'tanggal_tagihan',
        'tanggal_bayar',
    ];

    protected $casts = [
        'tanggal_tagihan' => 'date',
        'tanggal_bayar' => 'date',
        'jumlah' => 'decimal:2',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
