<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ServerStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ip'       => ['required', 'ip'],
            'user'     => ['required', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'max:100'],
            'lokasi'   => ['required', 'string', 'max:100'],
            'no_int'   => ['nullable', 'string', 'max:50'],
            'mikrotik' => ['nullable', 'string', 'max:50'],
            'remark1'  => ['nullable', 'string', 'max:255'],
            'remark2'  => ['nullable', 'string', 'max:255'],
            'remark3'  => ['nullable', 'string', 'max:255'],
        ];
    }

    public function sanitized(): array
    {
        return $this->only([
            'ip',
            'user',
            'password',
            'lokasi',
            'no_int',
            'mikrotik',
            'remark1',
            'remark2',
            'remark3'
        ]);
    }
}
