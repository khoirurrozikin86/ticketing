<?php
// app/Http/Requests/Super/LeadUpdateRequest.php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class LeadUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'name'    => 'required|string|max:150',
            'email'   => 'required|email:rfc,dns|max:150',
            'company' => 'nullable|string|max:150',
            'message' => 'required|string',
            'status'  => 'nullable|string|in:new,contacted,closed',
        ];
    }
    public function sanitized(): array
    {
        return [
            'name'    => $this->input('name'),
            'email'   => $this->input('email'),
            'company' => $this->input('company'),
            'message' => $this->input('message'),
            'status'  => $this->input('status', 'new'),
        ];
    }
}
