<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nonaktifkan foreign key checks sementara
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Hapus semua data yang ada
        DB::table('servers')->truncate();

        // Aktifkan kembali foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $now = now(); // Waktu saat ini

        $servers = [
            [
                'id' => 1,
                'ip' => '192.168.52.1',
                'user' => 'roziapi',
                'password' => 'Semarang123',
                'lokasi' => 'ASRI',
                'no_int' => '141459101672',
                'mikrotik' => 'ac2',
                'remark1' => 'wms tari ningshing',
                'remark2' => null,
                'remark3' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 2,
                'ip' => '80.80.80.1',
                'user' => 'roziapi',
                'password' => 'Semarang123',
                'lokasi' => 'NAMAR',
                'no_int' => '141459104641',
                'mikrotik' => 'ac2',
                'remark1' => 'toko ari',
                'remark2' => null,
                'remark3' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 4,
                'ip' => '91.91.91.1',
                'user' => 'roziapi',
                'password' => 'Semarang123',
                'lokasi' => 'BREYON',
                'no_int' => '142435109977',
                'mikrotik' => 'ac2',
                'remark1' => 'aca cel',
                'remark2' => null,
                'remark3' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 5,
                'ip' => '192.168.63.200',
                'user' => 'roziapi',
                'password' => 'Semarang123',
                'lokasi' => 'TLOGO',
                'no_int' => '142434110799',
                'mikrotik' => 'ac2',
                'remark1' => 'toko dika',
                'remark2' => null,
                'remark3' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 6,
                'ip' => '31.31.31.1',
                'user' => 'roziapi',
                'password' => 'Semarang123',
                'lokasi' => 'HERI',
                'no_int' => '141459102904',
                'mikrotik' => 'ac2',
                'remark1' => 'air minum',
                'remark2' => null,
                'remark3' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 7,
                'ip' => '192.168.50.1',
                'user' => 'roziapi',
                'password' => 'Semarang123',
                'lokasi' => 'SININ',
                'no_int' => '0',
                'mikrotik' => null,
                'remark1' => null,
                'remark2' => null,
                'remark3' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 8,
                'ip' => '50.50.50.1',
                'user' => 'roziapi',
                'password' => 'Semarang123',
                'lokasi' => 'DEREKAN',
                'no_int' => '141459101860',
                'mikrotik' => 'ac2',
                'remark1' => 'nerina',
                'remark2' => '1,41E+11',
                'remark3' => 'derekan',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 9,
                'ip' => '192.168.82.1',
                'user' => 'roziapi',
                'password' => 'Semarang123',
                'lokasi' => 'BEROKAN',
                'no_int' => '0',
                'mikrotik' => null,
                'remark1' => null,
                'remark2' => null,
                'remark3' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 12,
                'ip' => '192.168.83.1',
                'user' => 'roziapi',
                'password' => 'Semarang123',
                'lokasi' => 'PABELAN',
                'no_int' => '142434110842',
                'mikrotik' => 'ac2',
                'remark1' => 'toko heri',
                'remark2' => null,
                'remark3' => null,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 13,
                'ip' => '192.168.201.200',
                'user' => 'roziapi',
                'password' => 'Semarang1231',
                'lokasi' => 'OZ',
                'no_int' => '141459102911',
                'mikrotik' => 'ac2',
                'remark1' => 'permak jeans',
                'remark2' => '1,46459E+11',
                'remark3' => 'orlinta',
                'created_at' => $now,
                'updated_at' => $now
            ]
        ];

        DB::table('servers')->insert($servers);

        $this->command->info('Data server berhasil di-seed!');
    }
}
