<?php
// app/Http/Requests/Admin/TagihanGenerateRequest.php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TagihanGenerateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_bulan'       => ['required', 'string', 'size:2', 'exists:bulans,id_bulan'],
            'tahun'          => ['required', 'integer', 'between:2000,2100'],
            'id_pelanggans'  => ['nullable', 'array'], // optional: subset
            'id_pelanggans.*' => ['string', 'exists:pelanggans,id_pelanggan'],
            'jumlah'         => ['nullable', 'numeric', 'min:0'], // override jumlah jika diisi
        ];
    }

    public function sanitized(): array
    {
        return [
            'id_bulan'      => $this->id_bulan,
            'tahun'         => (int) $this->tahun,
            'id_pelanggans' => $this->id_pelanggans ?: null,
            'jumlah'        => $this->filled('jumlah') ? (float) $this->jumlah : null,
        ];
    }
}
