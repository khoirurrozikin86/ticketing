<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PaketStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_paket'  => ['required', 'string', 'max:100', 'unique:pakets,id_paket'],
            'nama'      => ['required', 'string', 'max:150'],
            'harga'     => ['required', 'numeric', 'min:0'],
            'kecepatan' => ['required', 'string', 'max:50'],
            'durasi'    => ['required', 'integer', 'min:1'],
            'remark1'   => ['nullable', 'string', 'max:255'],
            'remark2'   => ['nullable', 'string', 'max:255'],
            'remark3'   => ['nullable', 'string', 'max:255'],
        ];
    }

    public function sanitized(): array
    {
        return $this->only([
            'id_paket',
            'nama',
            'harga',
            'kecepatan',
            'durasi',
            'remark1',
            'remark2',
            'remark3'
        ]);
    }
}
