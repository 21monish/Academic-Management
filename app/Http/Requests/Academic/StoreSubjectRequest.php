<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', 'unique:subjects,code'],
            'name' => ['required', 'string', 'max:200'],
            'type' => ['required', 'in:Theory,Lab,Tutorial'],
            'category' => ['required', 'in:Core,Elective,Open Elective,Audit'],
            'credits' => ['nullable', 'integer', 'min:0'],
            'theory_hours' => ['nullable', 'integer', 'min:0'],
            'practical_hours' => ['nullable', 'integer', 'min:0'],
            'tutorial_hours' => ['nullable', 'integer', 'min:0'],
            'department_id' => ['required', 'exists:departments,dept_id'],
            'programme_id' => ['required', 'exists:programmes,programme_id'],
            'semester_id' => ['required', 'exists:semesters,semester_id'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'code' => Str::upper((string) $this->input('code')),
            'name' => trim((string) $this->input('name')),
        ]);
    }
}

