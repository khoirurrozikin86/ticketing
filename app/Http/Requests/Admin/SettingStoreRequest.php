<?php

// app/Http/Requests/Super/SettingStoreRequest.php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SettingStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'key'        => 'required|string|max:100|unique:settings,key',
            'value'      => 'nullable|string',    // JSON string (akan di-decode)
            'logo'       => 'nullable|image|max:2048', // optional
        ];
    }
}
