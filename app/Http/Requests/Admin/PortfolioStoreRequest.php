<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PortfolioStoreRequest extends FormRequest
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
            'thumb'   => 'nullable|image|max:2048', // 2MB
            'tags'    => 'nullable|string',         // "CMS, POS"
            'order'   => 'nullable|integer|min:0',
        ];
    }
}
