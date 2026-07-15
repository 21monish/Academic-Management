<?php

namespace App\Support;

class ValidationRules
{
    public static function phone(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'regex:/^[0-9]{10}$/',
        ];
    }

    public static function email(bool $required = false, int $max = 150): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'lowercase',
            'email:rfc',
            'max:'.$max,
        ];
    }

    public static function shortText(bool $required = false, int $max = 80): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'max:'.$max,
        ];
    }
}
