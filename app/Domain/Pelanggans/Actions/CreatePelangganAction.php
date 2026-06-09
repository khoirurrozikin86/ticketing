<?php
// app/Domain/Pelanggans/Actions/CreatePelangganAction.php
namespace App\Domain\Pelanggans\Actions;

use App\Domain\Pelanggans\DTOs\PelangganData;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreatePelangganAction
{
    public function __invoke(PelangganData $data): Pelanggan
    {
        $payload = $data->toArray();
        // hash password wajib saat create
        $payload['password'] = Hash::make((string)$payload['password']);

        return DB::transaction(fn() => Pelanggan::create($payload));
    }
}
