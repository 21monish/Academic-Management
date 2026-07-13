<?php

namespace App\Http\Requests\Academic;

use App\Models\Programme;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProgrammeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Programme|null $programme */
        $programme = $this->route('programme');
        $programmeId = $programme?->programme_id;

        return [
            'dept_id' => ['required', 'exists:departments,dept_id'],
            'code' => ['required', 'string', 'max:20', 'unique:programmes,code,' . $programmeId . ',programme_id'],
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

