<?php

namespace App\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;

class StoreSemesterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized.
     */
    public function authorize(): bool
    {
        return true;
        // If using permissions:
        // return hasPermission('semester.create');
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [
            'programme_id' => [
                'required',
                'exists:programmes,id',
            ],

            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'semester_no' => [
                'required',
                'integer',
                'min:1',
                'max:12',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    /**
     * Custom messages.
     */
    public function messages(): array
    {
        return [
            'programme_id.required' => 'Please select a programme.',
            'programme_id.exists'   => 'Selected programme is invalid.',

            'name.required'         => 'Semester name is required.',
            'name.max'              => 'Semester name may not exceed 100 characters.',

            'semester_no.required'  => 'Semester number is required.',
            'semester_no.integer'   => 'Semester number must be numeric.',
            'semester_no.min'       => 'Semester number must be at least 1.',
            'semester_no.max'       => 'Semester number may not exceed 12.',
        ];
    }
}