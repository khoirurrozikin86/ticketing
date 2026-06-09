<?php
// app/Http/Requests/Admin/PelangganUpdateRequest.php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PelangganUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('pelanggan')?->id ?? $this->route('id');

        return [
            'id_pelanggan'    => ['required', 'string', 'max:100', Rule::unique('pelanggans', 'id_pelanggan')->ignore($id)],
            'nama'            => ['required', 'string', 'max:150'],
            'alamat'          => ['nullable', 'string'],
            'no_hp'           => ['nullable', 'string', 'max:50'],
            'email'           => ['nullable', 'email', 'max:150'],
            'password'        => ['nullable', 'string', 'min:6', 'max:100'], // boleh kosong → abaikan
            'id_paket'        => ['nullable', 'integer', 'exists:pakets,id'],
            'ip_router'       => ['nullable', 'string', 'max:100'],
            'ip_parent_router' => ['nullable', 'string', 'max:100'],
            'remark1'         => ['nullable', 'string', 'max:255'],
            'remark2'         => ['nullable', 'string', 'max:255'],
            'remark3'         => ['nullable', 'string', 'max:255'],
            'id_server'       => ['nullable', 'integer', 'exists:servers,id'],
        ];
    }

    public function sanitized(): array
    {
        return $this->only([
            'id_pelanggan',
            'nama',
            'alamat',
            'no_hp',
            'email',
            'password',
            'id_paket',
            'ip_router',
            'ip_parent_router',
            'remark1',
            'remark2',
            'remark3',
            'id_server'
        ]);
    }
}
