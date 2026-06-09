<?php

namespace App\Domain\Tagihans\Services;

use App\Domain\Tagihans\DTOs\TagihanData;
use App\Domain\Tagihans\Actions\{
    CreateTagihanAction,
    UpdateTagihanAction,
    DeleteTagihanAction,
    GenerateTagihanBatchAction
};
use App\Domain\Tagihans\Support\InvoiceNumber;
use App\Models\Tagihan;
use Illuminate\Auth\Access\AuthorizationException;

class TagihanService
{
    public function __construct(
        protected CreateTagihanAction $create,
        protected UpdateTagihanAction $update,
        protected DeleteTagihanAction $delete,
        protected GenerateTagihanBatchAction $generateBatch,

    ) {}

    public function create(array $payload): Tagihan
    {
        // jika no_tagihan kosong → generate
        if (empty($payload['no_tagihan']) && !empty($payload['tahun']) && !empty($payload['id_bulan'])) {
            $payload['no_tagihan'] = InvoiceNumber::make((int)$payload['tahun'], (string)$payload['id_bulan']);
        }
        return ($this->create)(TagihanData::fromArray($payload));
    }

    public function update(Tagihan $tagihan, array $payload): Tagihan
    {
        return ($this->update)($tagihan, TagihanData::fromArray($payload));
    }

    public function delete(Tagihan $tagihan): void
    {
        ($this->delete)($tagihan);
    }

    public function generateBatch(int $tahun, string $idBulan, ?array $idPelanggans = null, ?float $overrideJumlah = null): array
    {
        return ($this->generateBatch)($tahun, $idBulan, $idPelanggans, $overrideJumlah);
    }
}
