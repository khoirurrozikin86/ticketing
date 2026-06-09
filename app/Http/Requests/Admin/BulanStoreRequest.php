<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BulanStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_bulan' => ['required', 'string', 'size:2', 'unique:bulans,id_bulan'],
            'bulan'    => ['required', 'string', 'max:20'],
        ];
    }

    public function sanitized(): array
    {
        return $this->only(['id_bulan', 'bulan']);
    }
}
