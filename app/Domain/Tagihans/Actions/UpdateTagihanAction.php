<?php
// app/Domain/Tagihans/Actions/UpdateTagihanAction.php
namespace App\Domain\Tagihans\Actions;

use App\Domain\Tagihans\DTOs\TagihanData;
use App\Models\Tagihan;
use Illuminate\Support\Facades\DB;

class UpdateTagihanAction
{
    public function __invoke(Tagihan $tagihan, TagihanData $data): Tagihan
    {
        return DB::transaction(function () use ($tagihan, $data) {
            $tagihan->update($data->toArray());
            return $tagihan->refresh();
        });
    }
}
