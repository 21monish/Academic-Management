<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Support\ValidationRules;

class StoreStudentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'student_type' => $this->input('student_type') ?: 'Regular',
        ]);
    }

    public function authorize(): bool
    {
        return true; // Controller enforces permission checks.
    }

    public function rules(): array
    {
        return [
            'college_id' => ['required', 'exists:colleges,college_id'],
            'programme_id' => ['required', 'exists:programmes,programme_id'],
            'category_id' => ['nullable', 'exists:categories,category_id'],
            'enrollment_no' => [
                'nullable',
                'string',
                'max:30',
                'unique:students,enrollment_no',
                Rule::unique('users', 'username'),
            ],

            'first_name' => ValidationRules::shortText(true, 80),
            'last_name' => ValidationRules::shortText(true, 80),
            'gender' => ['nullable', 'in:Male,Female,Other'],
            'dob' => ['required', 'date'],

            'phone' => [...ValidationRules::phone(), 'unique:students,phone'],
            'email' => [...ValidationRules::email(false, 150), 'unique:students,email'],

            'address' => ['nullable', 'string'],
            'guardian_name' => ValidationRules::shortText(false, 150),
            'guardian_phone' => ValidationRules::phone(),
            'admission_date' => ['nullable', 'date'],
            'student_type' => ['required', 'in:Regular,D2D,C2D'],
            'admission_type' => ['nullable', 'in:Direct,ACPC,Management'],

            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'enrollment_no.unique' => 'Enrollment No already exists.',
            'email.unique' => 'Email already exists.',
            'phone.unique' => 'Mobile already exists.',
        ];
    }
}

