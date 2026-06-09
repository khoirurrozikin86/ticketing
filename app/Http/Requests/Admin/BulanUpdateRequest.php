<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulanUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('bulan')?->id_bulan ?? $this->route('id_bulan');

        return [
            'id_bulan' => ['required', 'string', 'size:2', Rule::unique('bulans', 'id_bulan')->ignore($id, 'id_bulan')],
            'bulan'    => ['required', 'string', 'max:20'],
        ];
    }

    public function sanitized(): array
    {
        return $this->only(['id_bulan', 'bulan']);
    }
}
