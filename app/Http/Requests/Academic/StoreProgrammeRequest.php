<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;

class StoreProgrammeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dept_id' => ['required', 'exists:departments,dept_id'],
            'code' => ['required', 'string', 'max:20', 'unique:programmes,code'],
            'name' => ['required', 'string', 'max:150'],
            'level' => ['required', 'in:UG,PG,Diploma,PhD'],
            'duration_semesters' => ['nullable', 'integer', 'min:0'],
            'total_credits' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'Programme code already exists.',
        ];
    }
}

