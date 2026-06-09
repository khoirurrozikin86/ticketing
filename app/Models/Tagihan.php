<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    protected $table = 'tagihans';

    protected $fillable = [
        'no_tagihan',
        'id_bulan',
        'tahun',
        'id_pelanggan',
        'jumlah_tagihan',
        'status',
        'tgl_bayar',
        'user_id',
        'remark1',
        'remark2',
        'remark3',
    ];

    protected $casts = [
        'tgl_bayar' => 'date',
        'tahun' => 'integer',
        'jumlah_tagihan' => 'decimal:2',
    ];

    /* =====================
     * 🔗 RELATIONSHIPS
     * ===================== */
    public function bulan()
    {
        return $this->belongsTo(Bulan::class, 'id_bulan', 'id_bulan');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class, 'tagihan_id');
    }

    /* =====================
     * 💰 ACCESSORS
     * ===================== */
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->pembayarans()->sum('amount');
    }

    public function getIsLunasAttribute(): bool
    {
        return $this->total_paid + 0.00001 >= (float) $this->jumlah_tagihan;
    }

    /* =====================
     * ⚙️ SCOPES
     * ===================== */
    public function scopeBelum($q)
    {
        return $q->where('status', 'belum');
    }

    public function scopeForUser($query, \App\Models\User $user)
    {
        // Jika user punya permission view-all → tampilkan semua lokasi
        if ($user->can('tagihans.view-all')) {
            return $query;
        }

        // Jika user belum punya server_id (belum di-set), kembalikan kosong
        if (!$user->server_id) {
            return $query->whereRaw('1=0');
        }

        // Filter berdasarkan server tempat pelanggan terhubung
        return $query->whereHas('pelanggan', function ($q) use ($user) {
            $q->where('id_server', $user->server_id);
        });
    }


    // app/Models/Tagihan.php
    public function getSisaTagihanAttribute(): float
    {
        return max(0, (float) $this->jumlah_tagihan - $this->total_paid);
    }
}
