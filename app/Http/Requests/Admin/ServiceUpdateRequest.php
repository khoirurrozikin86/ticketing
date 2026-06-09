<?php

// app/Http/Requests/Super/ServiceUpdateRequest.php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ServiceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'name'    => 'required|string|max:150',
            'icon'    => 'nullable|string|max:255',
            'excerpt' => 'nullable|string',
            'meta'    => 'nullable|string',
            'order'   => 'nullable|integer|min:0',
        ];
    }
}
