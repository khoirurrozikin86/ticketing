<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    protected $table = 'servers';

    protected $fillable = [
        'ip',
        'user',
        'password',
        'lokasi',
        'no_int',
        'mikrotik',
        'remark1',
        'remark2',
        'remark3',
    ];

    protected $hidden = ['password']; // jangan pernah kirim ke JSON/Resource tanpa niat
}
