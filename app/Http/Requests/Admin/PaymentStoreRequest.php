<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PaymentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tagihan_id' => ['required', 'integer', 'exists:tagihans,id'],
            'amount'     => ['required', 'numeric', 'min:0.01'],
            'paid_at'    => ['required', 'date'],
            'method'     => ['nullable', 'string', 'max:50'],
            'ref_no'     => ['nullable', 'string', 'max:100'],
            'note'       => ['nullable', 'string'],
        ];
    }

    public function sanitized(): array
    {
        $data = $this->only(['tagihan_id', 'amount', 'paid_at', 'method', 'ref_no', 'note']);
        $data['user_id'] = optional($this->user())->id;
        return $data;
    }
}
