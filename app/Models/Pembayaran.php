<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayarans';
    protected $fillable = ['tagihan_id', 'amount', 'paid_at', 'method', 'ref_no', 'note', 'user_id'];
    protected $casts = ['paid_at' => 'date', 'amount' => 'decimal:2'];

    public function tagihan()
    {
        return $this->belongsTo(\App\Models\Tagihan::class);
    }
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
