<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Programme;
use App\Models\Student;
use App\Models\User;
use App\Support\ValidationRules;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class StudentImportService
{
    public function __construct(
        protected StudentService $students,
        protected AccessScopeService $accessScope
    ) {
    }

    /**
     * @return array{created:int, failed:int, errors:array<int, string>}
     */
    public function import(UploadedFile $file, Request $request): array
    {
        $rows = $this->readRows($file);

        if (count($rows) < 2) {
            throw ValidationException::withMessages([
                'file' => 'The import file must contain a header row and at least one student row.',
            ]);
        }

        $headers = $this->normalizeHeaders(array_shift($rows));
        $created = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $data = $this->rowToAssoc($headers, $row);

            if ($this->isEmptyRow($data)) {
                continue;
            }

            try {
                $payload = $this->payload($data, $request);
                $validator = Validator::make($payload, $this->rules());

                if ($validator->fails()) {
                    $errors[] = 'Row '.$rowNumber.': '.$validator->errors()->first();
                    continue;
                }

                $this->students->create($payload, $request);
                $created++;
            } catch (ValidationException $exception) {
                $errors[] = 'Row '.$rowNumber.': '.collect($exception->errors())->flatten()->first();
            } catch (\Throwable $exception) {
                report($exception);
                $errors[] = 'Row '.$rowNumber.': Could not import this student.';
            }
        }

        if ($created === 0 && $errors !== []) {
            throw ValidationException::withMessages([
                'file' => $errors[0],
            ]);
        }

        return [
            'created' => $created,
            'failed' => count($errors),
            'errors' => array_slice($errors, 0, 5),
        ];
    }

    public function templateRows(): array
    {
        return [
            [
                'programme_code',
                'enrollment_no',
                'first_name',
                'last_name',
                'gender',
                'dob',
                'phone',
                'email',
                'category_code',
                'student_type',
                'admission_type',
                'admission_date',
                'guardian_name',
                'guardian_phone',
                'address',
            ],
            [
                'BECE',
                '',
                'Asha',
                'Patel',
                'Female',
                '2005-04-21',
                '9876543210',
                'asha.patel@example.com',
                'GEN',
                'Regular',
                'ACPC',
                now()->format('Y-m-d'),
                'Ramesh Patel',
                '9876543211',
                'Ahmedabad',
            ],
        ];
    }

    private function payload(array $row, Request $request): array
    {
        $programme = $this->resolveProgramme($row, $request);
        $category = $this->resolveCategory($row);

        return [
            'college_id' => $programme->department?->college_id,
            'programme_id' => $programme->programme_id,
            'category_id' => $category?->category_id,
            'enrollment_no' => $this->blankToNull($row['enrollment_no'] ?? null),
            'first_name' => $this->value($row, 'first_name'),
            'last_name' => $this->value($row, 'last_name'),
            'gender' => $this->normalizeChoice($row['gender'] ?? null, ['Male', 'Female', 'Other']),
            'dob' => $this->normalizeDate($row['dob'] ?? null),
            'phone' => $this->digits($row['phone'] ?? null),
            'email' => $this->blankToNull($row['email'] ?? null),
            'address' => $this->blankToNull($row['address'] ?? null),
            'guardian_name' => $this->blankToNull($row['guardian_name'] ?? null),
            'guardian_phone' => $this->digits($row['guardian_phone'] ?? null),
            'admission_date' => $this->normalizeDate($row['admission_date'] ?? null),
            'student_type' => $this->normalizeStudentType($row['student_type'] ?? null),
            'admission_type' => $this->normalizeChoice($row['admission_type'] ?? null, ['Direct', 'ACPC', 'Management']),
            'is_active' => true,
        ];
    }

    private function rules(): array
    {
        return [
            'college_id' => ['required', 'exists:colleges,college_id'],
            'programme_id' => ['required', 'exists:programmes,programme_id'],
            'category_id' => ['nullable', 'exists:categories,category_id'],
            'enrollment_no' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique(Student::class, 'enrollment_no'),
                Rule::unique(User::class, 'username'),
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
            'is_active' => ['boolean'],
        ];
    }

    private function resolveProgramme(array $row, Request $request): Programme
    {
        $query = $this->accessScope->applyToProgrammes(
            Programme::query()->with('department.college'),
            $request->user()
        );

        if ($this->filled($row['programme_id'] ?? null)) {
            $query->where('programme_id', (int) $row['programme_id']);
        } else {
            $code = $this->value($row, 'programme_code') ?: $this->value($row, 'programme');
            $query->where(function ($query) use ($code) {
                $query->where('code', $code)
                    ->orWhere('name', $code);
            });
        }

        $programme = $query->first();

        if (! $programme) {
            throw ValidationException::withMessages([
                'programme_code' => 'Programme not found or not allowed for this user.',
            ]);
        }

        return $programme;
    }

    private function resolveCategory(array $row): ?Category
    {
        if ($this->filled($row['category_id'] ?? null)) {
            return Category::query()->find((int) $row['category_id']);
        }

        $value = $this->value($row, 'category_code') ?: $this->value($row, 'category');

        if ($value === '') {
            return null;
        }

        $category = Category::query()
            ->where('code', $value)
            ->orWhere('name', $value)
            ->first();

        if (! $category) {
            throw ValidationException::withMessages([
                'category_code' => 'Category not found.',
            ]);
        }

        return $category;
    }

    private function readRows(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return $extension === 'xlsx'
            ? $this->readXlsx($file)
            : $this->readCsv($file);
    }

    private function readCsv(UploadedFile $file): array
    {
        $rows = [];
        $handle = fopen($file->getRealPath(), 'r');

        if (! $handle) {
            return $rows;
        }

        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    private function readXlsx(UploadedFile $file): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw ValidationException::withMessages([
                'file' => 'XLSX import requires the PHP Zip extension. Please enable it or upload CSV.',
            ]);
        }

        $zip = new ZipArchive();

        if ($zip->open($file->getRealPath()) !== true) {
            throw ValidationException::withMessages([
                'file' => 'Could not read the Excel file.',
            ]);
        }

        $sharedStrings = $this->xlsxSharedStrings($zip);
        $sheetName = $this->firstWorksheetName($zip);
        $sheetXml = $sheetName ? $zip->getFromName($sheetName) : false;
        $zip->close();

        if (! $sheetXml) {
            throw ValidationException::withMessages([
                'file' => 'The Excel file does not contain a readable worksheet.',
            ]);
        }

        $xml = simplexml_load_string($sheetXml);

        if (! $xml) {
            return [];
        }

        $rows = [];

        foreach ($xml->xpath('//*[local-name()="sheetData"]/*[local-name()="row"]') ?: [] as $row) {
            $values = [];

            foreach ($row->xpath('*[local-name()="c"]') ?: [] as $cell) {
                $ref = (string) ($cell->attributes()['r'] ?? '');
                $columnIndex = $this->columnIndex($ref);
                $values[$columnIndex] = $this->xlsxCellValue($cell, $sharedStrings);
            }

            if ($values !== []) {
                ksort($values);
                $rows[] = $this->denseRow($values);
            }
        }

        return $rows;
    }

    private function xlsxSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if (! $xml) {
            return [];
        }

        $strings = [];
        $shared = simplexml_load_string($xml);

        foreach ($shared?->xpath('//*[local-name()="si"]') ?: [] as $item) {
            $parts = [];

            foreach ($item->xpath('.//*[local-name()="t"]') ?: [] as $text) {
                $parts[] = (string) $text;
            }

            $strings[] = implode('', $parts);
        }

        return $strings;
    }

    private function firstWorksheetName(ZipArchive $zip): ?string
    {
        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = $zip->getNameIndex($index);

            if (is_string($name) && preg_match('#^xl/worksheets/sheet\d+\.xml$#', $name)) {
                return $name;
            }
        }

        return null;
    }

    private function xlsxCellValue(\SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) ($cell->attributes()['t'] ?? '');
        $valueNode = $cell->xpath('*[local-name()="v"]')[0] ?? null;
        $value = $valueNode ? (string) $valueNode : '';

        if ($type === 's') {
            return $sharedStrings[(int) $value] ?? '';
        }

        if ($type === 'inlineStr') {
            $texts = $cell->xpath('.//*[local-name()="t"]') ?: [];

            return collect($texts)->map(fn ($text) => (string) $text)->join('');
        }

        return $value;
    }

    private function columnIndex(string $cellReference): int
    {
        if (! preg_match('/([A-Z]+)/i', $cellReference, $matches)) {
            return 0;
        }

        $letters = strtoupper($matches[1]);
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return $index - 1;
    }

    private function denseRow(array $values): array
    {
        $max = max(array_keys($values));
        $row = [];

        for ($index = 0; $index <= $max; $index++) {
            $row[] = $values[$index] ?? '';
        }

        return $row;
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map(function ($header) {
            $header = strtolower(trim((string) $header));
            $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);
            $header = preg_replace('/[^a-z0-9]+/', '_', $header);
            $header = trim((string) $header, '_');

            return [
                'enrollment' => 'enrollment_no',
                'enrolment' => 'enrollment_no',
                'enrollment_number' => 'enrollment_no',
                'enrolment_number' => 'enrollment_no',
                'mobile' => 'phone',
                'mobile_no' => 'phone',
                'contact' => 'phone',
                'programme' => 'programme_code',
                'program' => 'programme_code',
                'program_code' => 'programme_code',
                'programme_name' => 'programme_code',
                'program_name' => 'programme_code',
                'category' => 'category_code',
                'category_name' => 'category_code',
                'dob_date' => 'dob',
                'date_of_birth' => 'dob',
                'studenttype' => 'student_type',
            ][$header] ?? $header;
        }, $headers);
    }

    private function rowToAssoc(array $headers, array $row): array
    {
        $data = [];

        foreach ($headers as $index => $header) {
            if ($header === '') {
                continue;
            }

            $data[$header] = trim((string) ($row[$index] ?? ''));
        }

        return $data;
    }

    private function isEmptyRow(array $data): bool
    {
        return collect($data)->every(fn ($value) => trim((string) $value) === '');
    }

    private function value(array $row, string $key): string
    {
        return trim((string) ($row[$key] ?? ''));
    }

    private function filled(mixed $value): bool
    {
        return trim((string) $value) !== '';
    }

    private function blankToNull(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function digits(mixed $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        return $digits === '' ? null : $digits;
    }

    private function normalizeChoice(mixed $value, array $allowed): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        foreach ($allowed as $option) {
            if (strcasecmp($value, $option) === 0) {
                return $option;
            }
        }

        return $value;
    }

    private function normalizeStudentType(mixed $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return 'Regular';
        }

        return match (strtolower($value)) {
            'd2d' => 'D2D',
            'c2d' => 'C2D',
            default => 'Regular',
        };
    }

    private function normalizeDate(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (is_numeric($value) && (float) $value > 25000) {
            return Carbon::create(1899, 12, 30)->addDays((int) $value)->format('Y-m-d');
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return $value;
        }
    }
}
