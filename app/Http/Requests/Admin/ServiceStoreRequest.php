<?php

// app/Http/Requests/Super/ServiceStoreRequest.php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ServiceStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'name'    => 'required|string|max:150',
            'icon'    => 'nullable|string|max:255', // lucide name atau svg path
            'excerpt' => 'nullable|string',
            'meta'    => 'nullable|string',        // JSON string; kita decode di controller
            'order'   => 'nullable|integer|min:0',
        ];
    }
}
