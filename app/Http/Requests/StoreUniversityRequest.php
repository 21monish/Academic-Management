<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUniversityRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('theme')) {
            $this->merge(['theme' => 'ocean']);
        }
    }

    public function authorize(): bool
    {
        return true; // tighten with a policy/permission check once roles are wired up
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'website' => ['nullable', 'url', 'max:200'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'theme' => ['required', 'in:ocean,royal,forest'],
            'upi_id' => ['nullable', 'string', 'max:100'],
            'upi_name' => ['nullable', 'string', 'max:150'],
            'upi_note_prefix' => ['nullable', 'string', 'max:100'],
            'established_date' => ['nullable', 'date'],
        ];
    }
}
