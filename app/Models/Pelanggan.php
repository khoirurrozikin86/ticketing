<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    protected $table = 'pelanggans';

    protected $fillable = [
        'id_pelanggan',
        'nama',
        'alamat',
        'no_hp',
        'email',
        'password',
        'id_paket',
        'ip_router',
        'ip_parent_router',
        'remark1',
        'remark2',
        'remark3',
        'id_server',
    ];

    protected $hidden = ['password'];

    public function paket()
    {
        return $this->belongsTo(\App\Models\Paket::class, 'id_paket');
    }
    public function server()
    {
        return $this->belongsTo(\App\Models\Server::class, 'id_server', 'id');
    }
}
