<?php
// app/Http/Requests/Super/TechStackUpdateRequest.php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TechStackUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        $id = $this->route('techstack')?->id ?? null;
        return [
            'name'  => "required|string|max:150|unique:tech_stacks,name,{$id}",
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
