<?php
// app/Http/Requests/Super/TechStackStoreRequest.php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TechStackStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'name'  => 'required|string|max:150|unique:tech_stacks,name',
            'order' => 'nullable|integer|min:0',
        ];
    }
    public function sanitized(): array
    {
        return [
            'name'  => $this->input('name'),
            'order' => (int) $this->input('order', 0),
        ];
    }
}
