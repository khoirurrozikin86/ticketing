<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paket extends Model
{
    protected $table = 'pakets';

    protected $fillable = [
        'id_paket',
        'nama',
        'harga',
        'kecepatan',
        'durasi',
        'remark1',
        'remark2',
        'remark3',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'durasi' => 'integer',
    ];
}
