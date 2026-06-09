<?php
// app/Http/Requests/Super/PageUpdateRequest.php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PageUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('page')?->id ?? null;
        return [
            'title'     => 'required|string|max:150',
            'slug'      => "required|string|max:150|alpha_dash|unique:pages,slug,{$id}",
            'content'   => 'nullable|json',
            'published' => 'nullable|boolean',
        ];
    }

    public function sanitized(): array
    {
        return [
            'title'     => $this->input('title'),
            'slug'      => $this->input('slug'),
            'content'   => $this->input('content'),
            'published' => (bool)$this->input('published', true),
        ];
    }
}
