<?php
// app/Http/Requests/Admin/TagihanUpdateRequest.php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TagihanUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('tagihan')?->id ?? $this->route('id');

        return [
            'no_tagihan'     => ['required', 'string', 'max:100', Rule::unique('tagihans', 'no_tagihan')->ignore($id)],
            'id_bulan'       => ['required', 'string', 'size:2', 'exists:bulans,id_bulan'],
            'tahun'          => ['required', 'integer', 'between:2000,2100'],
            'id_pelanggan'   => ['required', 'string', 'exists:pelanggans,id_pelanggan'],
            'jumlah_tagihan' => ['required', 'numeric', 'min:0'],
            'status'         => ['required', 'in:belum,lunas'],
            'tgl_bayar'      => ['nullable', 'date'],
            'user_id'        => ['nullable', 'integer', 'exists:users,id'],
            'remark1'        => ['nullable', 'string', 'max:255'],
            'remark2'        => ['nullable', 'string', 'max:255'],
            'remark3'        => ['nullable', 'string', 'max:255'],
        ];
    }

    public function sanitized(): array
    {
        return $this->only([
            'no_tagihan',
            'id_bulan',
            'tahun',
            'id_pelanggan',
            'jumlah_tagihan',
            'status',
            'tgl_bayar',
            'user_id',
            'remark1',
            'remark2',
            'remark3',
        ]);
    }
}
