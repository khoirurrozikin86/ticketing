<?php
// app/Domain/Tagihans/Actions/CreateTagihanAction.php
namespace App\Domain\Tagihans\Actions;

use App\Domain\Tagihans\DTOs\TagihanData;
use App\Models\Tagihan;
use Illuminate\Support\Facades\DB;

class CreateTagihanAction
{
    public function __invoke(TagihanData $data): Tagihan
    {
        return DB::transaction(fn() => Tagihan::create($data->toArray()));
    }
}
