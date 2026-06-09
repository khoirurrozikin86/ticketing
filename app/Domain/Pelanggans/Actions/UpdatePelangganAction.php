<?php
// app/Domain/Pelanggans/Actions/UpdatePelangganAction.php
namespace App\Domain\Pelanggans\Actions;

use App\Domain\Pelanggans\DTOs\PelangganData;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UpdatePelangganAction
{
    public function __invoke(Pelanggan $pelanggan, PelangganData $data): Pelanggan
    {
        $payload = $data->toArray();

        // jika password null/'' → jangan update
        if (!array_key_exists('password', $payload) || $payload['password'] === '' || $payload['password'] === null) {
            unset($payload['password']);
        } else {
            $payload['password'] = Hash::make((string)$payload['password']);
        }

        return DB::transaction(function () use ($pelanggan, $payload) {
            $pelanggan->update($payload);
            return $pelanggan->refresh();
        });
    }
}
