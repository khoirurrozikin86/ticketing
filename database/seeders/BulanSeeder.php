<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BulanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('bulans')->delete();

        $now = now();

        $bulans = [
            ['id_bulan' => '01', 'bulan' => 'Januari'],
            ['id_bulan' => '02', 'bulan' => 'Februari'],
            ['id_bulan' => '03', 'bulan' => 'Maret'],
            ['id_bulan' => '04', 'bulan' => 'April'],
            ['id_bulan' => '05', 'bulan' => 'Mei'],
            ['id_bulan' => '06', 'bulan' => 'Juni'],
            ['id_bulan' => '07', 'bulan' => 'Juli'],
            ['id_bulan' => '08', 'bulan' => 'Agustus'],
            ['id_bulan' => '09', 'bulan' => 'September'],
            ['id_bulan' => '10', 'bulan' => 'Oktober'],
            ['id_bulan' => '11', 'bulan' => 'November'],
            ['id_bulan' => '12', 'bulan' => 'Desember'],
        ];

        $data = array_map(fn($item) => array_merge($item, [
            'created_at' => $now,
            'updated_at' => $now,
        ]), $bulans);

        DB::table('bulans')->insert($data);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
