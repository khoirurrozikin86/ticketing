<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bulan extends Model
{
    protected $table = 'bulans';
    protected $primaryKey = 'id_bulan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id_bulan', 'bulan'];
}
