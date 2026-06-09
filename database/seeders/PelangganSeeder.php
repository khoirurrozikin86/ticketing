<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PelangganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Kosongkan tabel pelangganss
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('pelanggans')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Cek keberadaan file CSV
        $csvPath = database_path('client.csv');

        if (!File::exists($csvPath)) {
            $this->command->error("File client.csv tidak ditemukan di [$csvPath]");
            return;
        }

        // 3. Buka file CSV
        if (($file = fopen($csvPath, 'r')) === false) {
            $this->command->error("Gagal membuka file CSV.");
            return;
        }

        $firstLine = true;
        $now = now();
        $batchSize = 100;
        $batchData = [];
        $totalRecords = 0;
        $progressBar = $this->command->getOutput()->createProgressBar();

        while (($data = fgetcsv($file, 2000, ';')) !== false) {
            // Lewati baris header
            if ($firstLine) {
                $firstLine = false;
                continue;
            }

            // Minimal validasi kolom (harus 14 kolom)
            if (count($data) < 14) {
                $this->command->warn("Baris dilewati karena jumlah kolom tidak sesuai: " . implode(',', $data));
                continue;
            }

            $batchData[] = [
                'id'                => $data[0],
                'id_pelanggan'      => $data[1],
                'nama'              => $data[2],
                'alamat'            => $data[3],
                'no_hp'             => $data[4] ?? '',
                'email'             => $data[5],
                'password'          => $data[6], // Encrypt jika perlu: Hash::make($data[6])
                'id_paket'          => $data[7],
                'ip_router'         => $data[8],
                'ip_parent_router'  => $data[9],
                'remark1'           => $data[10] ?? '',
                'remark2'           => $data[11] ?? '',
                'remark3'           => $data[12] ?? '',
                'id_server'         => $data[13],
                'created_at'        => $now,
                'updated_at'        => $now,
            ];

            $totalRecords++;
            $progressBar->advance();

            if (count($batchData) >= $batchSize) {
                DB::table('pelanggans')->insert($batchData);
                $batchData = [];
            }
        }

        if (!empty($batchData)) {
            DB::table('pelanggans')->insert($batchData);
        }

        fclose($file);
        $progressBar->finish();

        $this->command->newLine(2);
        $this->command->info("Import selesai!");
        $this->command->info("Total data diproses: $totalRecords record");
    }
}
