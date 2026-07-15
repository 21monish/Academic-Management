<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\ValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => [
                'nullable',
                'string',
                'max:80',
                Rule::unique(User::class, 'username')->ignore($this->user()->user_id, 'user_id'),
            ],
            'name' => ValidationRules::shortText(false, 80),
            'email' => [
                ...ValidationRules::email(true, 150),
                Rule::unique(User::class, 'email')->ignore($this->user()->user_id, 'user_id'),
            ],
        ];
    }
}
