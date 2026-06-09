<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus semua data yang ada terlebih dahulu
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('pakets')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $now = now(); // Waktu saat ini

        $pakets = [
            [
                'id' => 11,
                'id_paket' => 'INT00001',
                'nama' => 'PAKET 50',
                'harga' => $this->parseHarga('50000,00'),
                'kecepatan' => '2MB',
                'durasi' => 30,
                'remark1' => null,
                'remark2' => null,
                'remark3' => null,
                'created_at' => '2025-05-11 23:36:53',
                'updated_at' => '2025-05-11 23:36:53'
            ],
            [
                'id' => 12,
                'id_paket' => 'INT00002',
                'nama' => 'PAKET 60',
                'harga' => $this->parseHarga('60000,00'),
                'kecepatan' => '2MB',
                'durasi' => 30,
                'remark1' => null,
                'remark2' => null,
                'remark3' => null,
                'created_at' => '2025-05-11 23:36:53',
                'updated_at' => '2025-05-11 23:36:53'
            ],
            [
                'id' => 13,
                'id_paket' => 'INT00003',
                'nama' => 'PAKET 70',
                'harga' => $this->parseHarga('70000,00'),
                'kecepatan' => '2MB',
                'durasi' => 30,
                'remark1' => null,
                'remark2' => null,
                'remark3' => null,
                'created_at' => '2025-05-11 23:36:53',
                'updated_at' => '2025-05-11 23:36:53'
            ],
            [
                'id' => 14,
                'id_paket' => 'INT00004',
                'nama' => 'PAKET 80',
                'harga' => $this->parseHarga('80000,00'),
                'kecepatan' => '4MB',
                'durasi' => 30,
                'remark1' => null,
                'remark2' => null,
                'remark3' => null,
                'created_at' => '2025-05-11 23:37:13',
                'updated_at' => '2025-05-11 23:37:13'
            ],
            [
                'id' => 15,
                'id_paket' => 'INT00005',
                'nama' => 'PAKET 100',
                'harga' => $this->parseHarga('100000,00'),
                'kecepatan' => '5MB',
                'durasi' => 30,
                'remark1' => null,
                'remark2' => null,
                'remark3' => null,
                'created_at' => '2025-05-11 23:37:32',
                'updated_at' => '2025-05-11 23:39:36'
            ],
            [
                'id' => 18,
                'id_paket' => 'INT00006',
                'nama' => 'PAKET 0',
                'harga' => $this->parseHarga('0,00'),
                'kecepatan' => '5MB',
                'durasi' => 30,
                'remark1' => null,
                'remark2' => null,
                'remark3' => null,
                'created_at' => '2025-05-13 05:35:49',
                'updated_at' => '2025-05-13 05:35:49'
            ],
            [
                'id' => 19,
                'id_paket' => 'INT00007',
                'nama' => 'PAKET 110',
                'harga' => $this->parseHarga('110000,00'),
                'kecepatan' => '3MB',
                'durasi' => 4,
                'remark1' => null,
                'remark2' => null,
                'remark3' => null,
                'created_at' => '2025-06-14 14:08:31',
                'updated_at' => '2025-06-14 14:08:31'
            ],
            [
                'id' => 20,
                'id_paket' => 'INT00008',
                'nama' => 'PAKET 120',
                'harga' => $this->parseHarga('120000,00'),
                'kecepatan' => '5MB',
                'durasi' => 4,
                'remark1' => null,
                'remark2' => null,
                'remark3' => null,
                'created_at' => '2025-06-14 14:08:31',
                'updated_at' => '2025-06-14 14:08:31'
            ],
            [
                'id' => 21,
                'id_paket' => 'INT00009',
                'nama' => 'PAKET 150',
                'harga' => $this->parseHarga('150000,00'),
                'kecepatan' => '5MB',
                'durasi' => 4,
                'remark1' => null,
                'remark2' => null,
                'remark3' => null,
                'created_at' => '2025-06-14 14:08:31',
                'updated_at' => '2025-06-14 14:08:31'
            ],
            [
                'id' => 22,
                'id_paket' => 'INT00010',
                'nama' => 'PAKET 200',
                'harga' => $this->parseHarga('200000,00'),
                'kecepatan' => '5MB',
                'durasi' => 4,
                'remark1' => null,
                'remark2' => null,
                'remark3' => null,
                'created_at' => '2025-06-14 14:08:31',
                'updated_at' => '2025-06-14 14:08:31'
            ]
        ];

        DB::table('pakets')->insert($pakets);

        $this->command->info('Paket berhasil di-seed!');
    }

    protected function parseHarga($value)
    {
        // Hapus titik ribuan (jika ada) dan ganti koma dengan titik
        $cleaned = str_replace(['.', ','], ['', '.'], $value);
        return (float) $cleaned;
    }
}
