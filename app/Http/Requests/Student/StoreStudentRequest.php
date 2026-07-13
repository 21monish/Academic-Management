<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
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
                'required',
                'string',
                'max:30',
                'unique:students,enrollment_no',
                Rule::unique('users', 'username'),
            ],

            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'gender' => ['nullable', 'in:Male,Female,Other'],
            'dob' => ['required', 'date'],

            'phone' => ['nullable', 'string', 'max:20', 'unique:students,phone'],
            'email' => ['nullable', 'email', 'max:150', 'unique:students,email'],

            'address' => ['nullable', 'string'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
            'admission_date' => ['nullable', 'date'],
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

