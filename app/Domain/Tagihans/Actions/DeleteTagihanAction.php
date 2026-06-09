<?php
// app/Domain/Tagihans/Actions/DeleteTagihanAction.php
namespace App\Domain\Tagihans\Actions;

use App\Models\Tagihan;
use Illuminate\Support\Facades\DB;

class DeleteTagihanAction
{
    public function __invoke(Tagihan $tagihan): void
    {
        DB::transaction(fn() => $tagihan->delete());
    }
}
