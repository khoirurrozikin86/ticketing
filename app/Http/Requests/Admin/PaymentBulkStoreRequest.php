<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PaymentBulkStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tagihan_ids' => 'required|array|min:1',
            'tagihan_ids.*' => 'exists:tagihans,id',

            'paid_at' => 'required|date',
            'method'  => 'required|string|max:20',
            'note'    => 'nullable|string|max:255',
        ];
    }

    public function sanitized(): array
    {
        return $this->validated();
    }
}
