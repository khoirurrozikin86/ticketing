<?php

// app/Http/Requests/Super/SettingUpdateRequest.php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SettingUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        $id = $this->route('setting')?->id ?? null;
        return [
            'key'   => "required|string|max:100|unique:settings,key,{$id}",
            'value' => 'nullable|string',
            'logo'  => 'nullable|image|max:2048',
        ];
    }
}
