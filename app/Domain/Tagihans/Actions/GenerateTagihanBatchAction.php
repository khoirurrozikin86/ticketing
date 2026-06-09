<?php

namespace App\Domain\Tagihans\Actions;

use App\Domain\Tagihans\Support\InvoiceNumber;
use App\Models\Pelanggan;
use App\Models\Tagihan;
use Illuminate\Support\Facades\DB;

class GenerateTagihanBatchAction
{
    /**
     * @param int $tahun
     * @param string $idBulan '01'..'12'
     * @param array<string>|null $pelangganIds  // id_pelanggan
     * @param float|null $overrideJumlah
     * @return array{created:int, skipped:int, items:\Illuminate\Support\Collection}
     */
    public function __invoke(int $tahun, string $idBulan, ?array $pelangganIds = null, ?float $overrideJumlah = null): array
    {
        $idBulan = str_pad($idBulan, 2, '0', STR_PAD_LEFT);

        // --- FIX: prefix kolom & alias, hindari ambiguity ---
        $pelQuery = Pelanggan::query()
            ->leftJoin('pakets', 'pakets.id', '=', 'pelanggans.id_paket')
            ->when($pelangganIds, fn($q) => $q->whereIn('pelanggans.id_pelanggan', $pelangganIds))
            ->where('pelanggans.remark1', 1) // ✅ hanya pelanggan aktif
            ->select([
                'pelanggans.id_pelanggan',
                'pelanggans.id_paket as paket_id',          // alias aman
                DB::raw('pakets.harga as harga_paket'),     // alias aman
                // kalau butuh kode paket string: DB::raw('pakets.id_paket as kode_paket'),
            ]);

        $pelanggans = $pelQuery->get();

        $created = 0;
        $skipped = 0;
        $items = collect();

        DB::transaction(function () use ($pelanggans, $tahun, $idBulan, $overrideJumlah, &$created, &$skipped, &$items) {
            foreach ($pelanggans as $p) {
                $exists = Tagihan::where([
                    'id_pelanggan' => $p->id_pelanggan,
                    'id_bulan'     => $idBulan,
                    'tahun'        => $tahun,
                ])->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                $jumlah = $overrideJumlah ?? (float) ($p->harga_paket ?? 0);
                $no = InvoiceNumber::make($tahun, $idBulan);

                $item = Tagihan::create([
                    'no_tagihan'     => $no,
                    'id_bulan'       => $idBulan,
                    'tahun'          => $tahun,
                    'id_pelanggan'   => $p->id_pelanggan,
                    'jumlah_tagihan' => $jumlah,
                    'status'         => 'belum',
                ]);

                $items->push($item);
                $created++;
            }
        });

        return compact('created', 'skipped', 'items');
    }
}
