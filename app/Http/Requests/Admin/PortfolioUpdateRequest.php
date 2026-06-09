<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PortfolioUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'   => 'required|string|max:160',
            'summary' => 'nullable|string',
            'thumb'   => 'nullable|image|max:2048',
            'tags'    => 'nullable|string',
            'order'   => 'nullable|integer|min:0',
        ];
    }
}
