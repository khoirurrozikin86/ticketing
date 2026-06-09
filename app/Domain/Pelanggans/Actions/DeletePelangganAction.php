<?php
// app/Domain/Pelanggans/Actions/DeletePelangganAction.php
namespace App\Domain\Pelanggans\Actions;

use App\Models\Pelanggan;
use Illuminate\Support\Facades\DB;

class DeletePelangganAction
{
    public function __invoke(Pelanggan $pelanggan): void
    {
        DB::transaction(fn() => $pelanggan->delete());
    }
}
