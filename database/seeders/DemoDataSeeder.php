<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    private const TARGET_ROWS = 5;

    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SystemSettingSeeder::class,
            SuperAdminSeeder::class,
            ChatbotKnowledgeSeeder::class,
        ]);

        Schema::disableForeignKeyConstraints();

        try {
            foreach ($this->tableNames() as $table) {
                if ($table === 'migrations') {
                    continue;
                }

                $this->fillTable($table);
            }

            $this->completeMissingInputs();
            $this->beautifyCoreDemoData();
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    private function fillTable(string $table): void
    {
        $attempt = 1;

        while (DB::table($table)->count() < self::TARGET_ROWS && $attempt <= 50) {
            $rowNumber = DB::table($table)->count() + 1;
            $row = $this->rowFor($table, $rowNumber, $attempt);

            if ($row === []) {
                break;
            }

            try {
                DB::table($table)->insertOrIgnore($row);
            } catch (\Throwable) {
                // Try a different row number for unique/composite edge cases.
            }

            $attempt++;
        }
    }

    private function rowFor(string $table, int $rowNumber, int $attempt): array
    {
        $columns = $this->columns($table);
        $foreignKeys = $this->foreignKeys($table);
        $uniqueColumns = $this->uniqueColumns($table);
        $row = [];

        foreach ($columns as $column) {
            $name = $column['name'];

            if ($this->shouldSkipColumn($column)) {
                continue;
            }

            if (isset($foreignKeys[$name])) {
                $row[$name] = $this->foreignKeyValue($foreignKeys[$name]['referenced_table'], $foreignKeys[$name]['referenced_column'], $rowNumber);
                continue;
            }

            $row[$name] = $this->valueFor($table, $column, $rowNumber, $attempt, in_array($name, $uniqueColumns, true));
        }

        return $this->normalizeSpecialRows($table, $row, $rowNumber);
    }

    private function normalizeSpecialRows(string $table, array $row, int $rowNumber): array
    {
        if ($table === 'users') {
            if (array_key_exists('name', $row)) {
                $row['name'] = "Demo User {$rowNumber}";
            }

            if (array_key_exists('username', $row)) {
                $row['username'] = "demo_user_{$rowNumber}";
            }

            if (array_key_exists('email', $row)) {
                $row['email'] = "demo.user{$rowNumber}@example.test";
            }

            if (array_key_exists('password', $row)) {
                $row['password'] = Hash::make('password');
            }

            if (array_key_exists('password_hash', $row)) {
                $row['password_hash'] = Hash::make('password');
            }

            if (array_key_exists('email_verified_at', $row)) {
                $row['email_verified_at'] = now();
            }

            if (array_key_exists('role_id', $row)) {
                $row['role_id'] = $this->foreignKeyValue('user_roles', 'role_id', min($rowNumber, 5));
            }
        }

        if ($table === 'user_roles') {
            $roles = ['Super Admin', 'Admin', 'Student'];
            $row['role_name'] = $roles[$rowNumber - 1] ?? "Demo Role {$rowNumber}";
        }

        if ($table === 'permissions') {
            $modules = ['dashboard', 'student', 'staff', 'attendance', 'fees'];
            $actions = ['view', 'create', 'update', 'delete', 'approve'];
            $row['module_name'] = $modules[$rowNumber - 1] ?? "demo_module_{$rowNumber}";
            $row['action'] = $actions[$rowNumber - 1] ?? 'view';
        }

        if ($table === 'categories') {
            $codes = ['GEN', 'SC', 'ST', 'OBC', 'EWS'];
            $row['code'] = $this->unusedCategoryCode($codes[$rowNumber - 1] ?? "CAT{$rowNumber}", $rowNumber);
        }

        if ($table === 'students') {
            $row['enrollment_no'] = $row['enrollment_no'] ?? 'DEMO'.str_pad((string) $rowNumber, 6, '0', STR_PAD_LEFT);
            $row['email'] = $row['email'] ?? "student{$rowNumber}@example.test";
            $row['first_name'] = $row['first_name'] ?? "Student{$rowNumber}";
            $row['last_name'] = $row['last_name'] ?? 'Demo';
        }

        if ($table === 'staff') {
            $row['employee_code'] = $row['employee_code'] ?? 'STF'.str_pad((string) $rowNumber, 4, '0', STR_PAD_LEFT);
            $row['email'] = $row['email'] ?? "staff{$rowNumber}@example.test";
            $row['first_name'] = $row['first_name'] ?? "Staff{$rowNumber}";
            $row['last_name'] = $row['last_name'] ?? 'Demo';
        }

        return $row;
    }

    private function unusedCategoryCode(string $preferredCode, int $rowNumber): string
    {
        if (! DB::table('categories')->where('code', $preferredCode)->exists()) {
            return $preferredCode;
        }

        for ($i = $rowNumber; $i < $rowNumber + 50; $i++) {
            $candidate = 'D'.str_pad((string) $i, 2, '0', STR_PAD_LEFT);

            if (! DB::table('categories')->where('code', $candidate)->exists()) {
                return $candidate;
            }
        }

        return 'DX'.$rowNumber;
    }

    private function valueFor(string $table, array $column, int $rowNumber, int $attempt, bool $unique): mixed
    {
        $name = $column['name'];
        $type = $column['type'];
        $seed = $unique ? "{$rowNumber}_{$attempt}" : (string) $rowNumber;

        if ($column['enum_values']) {
            return $this->enumValue($column['enum_values'], $name, $rowNumber);
        }

        if (Str::contains($name, 'email')) {
            return "{$table}.{$name}.{$seed}@example.test";
        }

        if (Str::contains($name, 'password')) {
            return Hash::make('password');
        }

        if (Str::contains($name, ['phone', 'mobile', 'contact'])) {
            return '900000'.str_pad((string) $rowNumber, 4, '0', STR_PAD_LEFT);
        }

        if (Str::contains($name, ['url', 'path'])) {
            return "uploads/demo/{$table}-{$seed}.txt";
        }

        return match (true) {
            Str::contains($type, ['int', 'bigint', 'smallint', 'tinyint']) => $this->integerValue($name, $rowNumber),
            Str::contains($type, ['decimal', 'double', 'float']) => round($rowNumber * 10.5, 2),
            Str::contains($type, ['bool']) || $type === 'tinyint(1)' => 1,
            Str::contains($type, ['date']) && ! Str::contains($type, ['datetime', 'timestamp']) => now()->addDays($rowNumber)->toDateString(),
            Str::contains($type, ['datetime', 'timestamp']) => now(),
            Str::contains($type, ['time']) => now()->setTime(9 + ($rowNumber % 6), 0)->format('H:i:s'),
            Str::contains($type, ['json']) => json_encode(['demo' => true, 'row' => $rowNumber]),
            Str::contains($type, ['text']) => "Demo {$name} {$seed}",
            default => $this->stringValue($table, $name, $seed, (int) ($column['length'] ?? 80)),
        };
    }

    private function integerValue(string $name, int $rowNumber): int
    {
        if (Str::contains($name, ['year'])) {
            return 2026;
        }

        if (Str::contains($name, ['semester_no'])) {
            return $rowNumber;
        }

        if (Str::contains($name, ['capacity', 'max_students', 'batch_size'])) {
            return 30 + $rowNumber;
        }

        if (Str::contains($name, ['marks', 'score', 'credits', 'lectures', 'seat_no', 'floor_no', 'batch_no'])) {
            return $rowNumber * 5;
        }

        return $rowNumber;
    }

    private function stringValue(string $table, string $name, string $seed, int $length): string
    {
        $value = match (true) {
            $name === 'academic_year' => '2026-27',
            $name === 'label' => "Demo {$seed}",
            Str::contains($name, ['code', 'no']) => strtoupper(Str::limit("D{$seed}", max(2, min($length, 12)), '')),
            Str::contains($name, ['name', 'title']) => Str::headline(str_replace('_', ' ', $table))." {$seed}",
            default => "Demo {$name} {$seed}",
        };

        return Str::limit($value, max(1, $length), '');
    }

    private function enumValue(array $values, string $column, int $rowNumber): string
    {
        $preferred = match ($column) {
            'status' => ['Active', 'Generated', 'Scheduled', 'Assigned', 'Present', 'Pending', 'Cleared'],
            'payment_status' => ['Paid', 'Partial', 'Unpaid', 'Overdue'],
            'result_status' => ['Pass', 'Fail', 'ATKT', 'Absent'],
            'lecture_type', 'subject_type', 'exam_type' => ['Theory', 'Lab', 'Both', 'Practical'],
            'gender' => ['Male', 'Female', 'Other'],
            'priority' => ['Normal', 'High', 'Low', 'Urgent'],
            'audience_type' => ['All', 'College', 'Dept', 'Programme', 'Semester', 'Role'],
            'day_of_week' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
            default => [],
        };

        foreach ($preferred as $value) {
            if (in_array($value, $values, true)) {
                return $value;
            }
        }

        return $values[($rowNumber - 1) % count($values)];
    }

    private function foreignKeyValue(string $table, string $column, int $rowNumber): int|string|null
    {
        $values = DB::table($table)->orderBy($column)->pluck($column)->values();

        if ($values->isEmpty()) {
            return $rowNumber;
        }

        return $values[($rowNumber - 1) % $values->count()];
    }

    private function shouldSkipColumn(array $column): bool
    {
        return $column['auto_increment']
            || ($column['nullable'] && $column['default'] !== null)
            || in_array($column['name'], ['remember_token'], true);
    }

    private function tableNames(): array
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return collect(DB::select("select name from sqlite_master where type = 'table' and name not like 'sqlite_%'"))
                ->pluck('name')
                ->sort()
                ->values()
                ->all();
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            return collect(DB::select('SHOW TABLES'))
                ->map(fn ($row) => array_values((array) $row)[0] ?? null)
                ->filter()
                ->sort()
                ->values()
                ->all();
        }

        return collect(DB::select("select table_name from information_schema.tables where table_schema = 'public'"))
            ->pluck('table_name')
            ->sort()
            ->values()
            ->all();
    }

    private function columns(string $table): array
    {
        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            return collect(DB::select(
                'select COLUMN_NAME, IS_NULLABLE, COLUMN_DEFAULT, DATA_TYPE, COLUMN_TYPE, CHARACTER_MAXIMUM_LENGTH, EXTRA
                 from information_schema.COLUMNS
                 where TABLE_SCHEMA = DATABASE() and TABLE_NAME = ?
                 order by ORDINAL_POSITION',
                [$table]
            ))->map(fn ($column) => [
                'name' => $column->COLUMN_NAME,
                'nullable' => $column->IS_NULLABLE === 'YES',
                'default' => $column->COLUMN_DEFAULT,
                'type' => strtolower($column->COLUMN_TYPE ?: $column->DATA_TYPE),
                'length' => $column->CHARACTER_MAXIMUM_LENGTH,
                'auto_increment' => str_contains(strtolower($column->EXTRA ?? ''), 'auto_increment'),
                'enum_values' => $this->parseEnumValues($column->COLUMN_TYPE ?? ''),
            ])->all();
        }

        return collect(Schema::getColumns($table))->map(fn ($column) => [
            'name' => $column['name'],
            'nullable' => (bool) ($column['nullable'] ?? false),
            'default' => $column['default'] ?? null,
            'type' => strtolower($column['type'] ?? $column['type_name'] ?? 'string'),
            'length' => $column['length'] ?? null,
            'auto_increment' => (bool) ($column['auto_increment'] ?? false),
            'enum_values' => [],
        ])->all();
    }

    private function foreignKeys(string $table): array
    {
        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            return collect(DB::select(
                'select COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                 from information_schema.KEY_COLUMN_USAGE
                 where TABLE_SCHEMA = DATABASE()
                    and TABLE_NAME = ?
                    and REFERENCED_TABLE_NAME is not null',
                [$table]
            ))->mapWithKeys(fn ($key) => [
                $key->COLUMN_NAME => [
                    'referenced_table' => $key->REFERENCED_TABLE_NAME,
                    'referenced_column' => $key->REFERENCED_COLUMN_NAME,
                ],
            ])->all();
        }

        return collect(Schema::getForeignKeys($table))
            ->mapWithKeys(function (array $key) {
                $column = $key['columns'][0] ?? null;
                $referencedTable = $key['foreign_table'] ?? null;
                $referencedColumn = $key['foreign_columns'][0] ?? null;

                if (! $column || ! $referencedTable || ! $referencedColumn) {
                    return [];
                }

                return [
                    $column => [
                        'referenced_table' => $referencedTable,
                        'referenced_column' => $referencedColumn,
                    ],
                ];
            })
            ->all();
    }

    private function uniqueColumns(string $table): array
    {
        $driver = DB::connection()->getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return [];
        }

        return collect(DB::select(
            'select COLUMN_NAME
             from information_schema.STATISTICS
             where TABLE_SCHEMA = DATABASE() and TABLE_NAME = ? and NON_UNIQUE = 0',
            [$table]
        ))->pluck('COLUMN_NAME')->unique()->values()->all();
    }

    private function parseEnumValues(string $columnType): array
    {
        if (! str_starts_with(strtolower($columnType), 'enum(')) {
            return [];
        }

        preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $columnType, $matches);

        return array_map(fn ($value) => stripcslashes($value), $matches[1] ?? []);
    }

    private function completeMissingInputs(): void
    {
        foreach ($this->tableNames() as $table) {
            if (in_array($table, ['migrations', 'users'], true)) {
                continue;
            }

            $primaryKey = $this->primaryKey($table);

            if (! $primaryKey) {
                continue;
            }

            $columns = collect($this->columns($table))->keyBy('name');
            $foreignKeys = $this->foreignKeys($table);
            $uniqueColumns = $this->uniqueColumns($table);
            $rows = DB::table($table)->orderBy($primaryKey)->limit(self::TARGET_ROWS)->get();

            foreach ($rows as $index => $rowObject) {
                $row = (array) $rowObject;
                $rowNumber = $index + 1;
                $updates = [];

                foreach ($columns as $name => $column) {
                    if ($name === $primaryKey || $column['auto_increment'] || in_array($name, ['remember_token'], true)) {
                        continue;
                    }

                    if (array_key_exists($name, $row) && ! is_null($row[$name]) && $row[$name] !== '') {
                        continue;
                    }

                    if (isset($foreignKeys[$name])) {
                        $value = $this->foreignKeyValue($foreignKeys[$name]['referenced_table'], $foreignKeys[$name]['referenced_column'], $rowNumber);
                    } else {
                        $value = $this->valueFor($table, $column, $rowNumber, $rowNumber, in_array($name, $uniqueColumns, true));
                    }

                    if (! is_null($value) && $value !== '') {
                        $updates[$name] = $value;
                    }
                }

                if ($updates !== []) {
                    DB::table($table)->where($primaryKey, $row[$primaryKey])->update($updates);
                }
            }
        }
    }

    private function beautifyCoreDemoData(): void
    {
        $this->updateByPosition('universities', 'university_id', [
            ['name' => 'Gujarat Technological University', 'address' => 'Nr. Vishwakarma Government Engineering College, Chandkheda, Ahmedabad', 'phone' => '07923267521', 'email' => 'info@gtu.example', 'website' => 'https://www.gtu.example', 'logo_url' => 'uploads/demo/gtu-logo.png', 'upi_id' => 'gtu@upi', 'upi_name' => 'Gujarat Technological University', 'upi_note_prefix' => 'GTU Fee', 'theme' => 'ocean', 'established_date' => '2007-05-16'],
            ['name' => 'Sardar Patel Institute University', 'address' => 'Vallabh Vidyanagar, Anand, Gujarat', 'phone' => '02692230000', 'email' => 'office@spiu.example', 'website' => 'https://spiu.example', 'logo_url' => 'uploads/demo/spiu-logo.png', 'upi_id' => 'spiu@upi', 'upi_name' => 'SPI University', 'upi_note_prefix' => 'SPIU Fee', 'theme' => 'royal', 'established_date' => '1998-06-01'],
            ['name' => 'Narmada Technical University', 'address' => 'Bharuch, Gujarat', 'phone' => '02642240000', 'email' => 'contact@ntu.example', 'website' => 'https://ntu.example', 'logo_url' => 'uploads/demo/ntu-logo.png', 'upi_id' => 'ntu@upi', 'upi_name' => 'Narmada Technical University', 'upi_note_prefix' => 'NTU Fee', 'theme' => 'forest', 'established_date' => '2010-07-15'],
            ['name' => 'Ahmedabad Engineering University', 'address' => 'Navrangpura, Ahmedabad, Gujarat', 'phone' => '07940001000', 'email' => 'admin@aeu.example', 'website' => 'https://aeu.example', 'logo_url' => 'uploads/demo/aeu-logo.png', 'upi_id' => 'aeu@upi', 'upi_name' => 'Ahmedabad Engineering University', 'upi_note_prefix' => 'AEU Fee', 'theme' => 'ocean', 'established_date' => '2003-08-01'],
            ['name' => 'Surat Innovation University', 'address' => 'Dumas Road, Surat, Gujarat', 'phone' => '02614000100', 'email' => 'hello@siu.example', 'website' => 'https://siu.example', 'logo_url' => 'uploads/demo/siu-logo.png', 'upi_id' => 'siu@upi', 'upi_name' => 'Surat Innovation University', 'upi_note_prefix' => 'SIU Fee', 'theme' => 'royal', 'established_date' => '2015-06-20'],
        ]);

        $this->updateByPosition('colleges', 'college_id', [
            ['code' => 'LDCE', 'name' => 'L. D. College of Engineering', 'address' => 'Navrangpura, Ahmedabad', 'phone' => '07926302887', 'email' => 'principal@ldce.example', 'principal_name' => 'Dr. Mehul Shah', 'affiliation_type' => 'Autonomous', 'college_type' => 'Government', 'affiliated_on' => '2008-06-01', 'is_active' => true],
            ['code' => 'VGEC', 'name' => 'Vishwakarma Government Engineering College', 'address' => 'Chandkheda, Ahmedabad', 'phone' => '07923293866', 'email' => 'office@vgec.example', 'principal_name' => 'Dr. Rina Patel', 'affiliation_type' => 'Affiliated', 'college_type' => 'Government', 'affiliated_on' => '2009-06-01', 'is_active' => true],
            ['code' => 'GCET', 'name' => 'G H Patel College of Engineering and Technology', 'address' => 'Vallabh Vidyanagar, Anand', 'phone' => '02692231725', 'email' => 'info@gcet.example', 'principal_name' => 'Dr. Ketan Desai', 'affiliation_type' => 'Affiliated', 'college_type' => 'Grant-in-Aid', 'affiliated_on' => '2010-06-01', 'is_active' => true],
            ['code' => 'SCET', 'name' => 'Sarvajanik College of Engineering and Technology', 'address' => 'Athwalines, Surat', 'phone' => '02612244400', 'email' => 'admin@scet.example', 'principal_name' => 'Dr. Neha Mehta', 'affiliation_type' => 'Autonomous', 'college_type' => 'Private', 'affiliated_on' => '2011-06-01', 'is_active' => true],
            ['code' => 'BVM', 'name' => 'Birla Vishvakarma Mahavidyalaya', 'address' => 'Vallabh Vidyanagar, Anand', 'phone' => '02692230104', 'email' => 'office@bvm.example', 'principal_name' => 'Dr. Amit Trivedi', 'affiliation_type' => 'Constituent', 'college_type' => 'Grant-in-Aid', 'affiliated_on' => '2008-06-01', 'is_active' => true],
        ]);

        $this->updateByPosition('departments', 'dept_id', [
            ['code' => 'CE', 'name' => 'Computer Engineering', 'description' => 'Software, systems, data, and AI academic department.', 'is_active' => true],
            ['code' => 'IT', 'name' => 'Information Technology', 'description' => 'Networks, web platforms, cloud, and information systems.', 'is_active' => true],
            ['code' => 'EC', 'name' => 'Electronics and Communication', 'description' => 'Embedded systems, VLSI, communication, and signal processing.', 'is_active' => true],
            ['code' => 'ME', 'name' => 'Mechanical Engineering', 'description' => 'Design, manufacturing, thermal, and production engineering.', 'is_active' => true],
            ['code' => 'CL', 'name' => 'Civil Engineering', 'description' => 'Structures, transportation, water resources, and construction.', 'is_active' => true],
        ]);

        $this->updateByPosition('programmes', 'programme_id', [
            ['code' => 'BE_CE', 'name' => 'Bachelor of Engineering - Computer Engineering', 'level' => 'UG', 'duration_semesters' => 8, 'total_credits' => 180, 'is_active' => true],
            ['code' => 'BE_IT', 'name' => 'Bachelor of Engineering - Information Technology', 'level' => 'UG', 'duration_semesters' => 8, 'total_credits' => 180, 'is_active' => true],
            ['code' => 'BE_EC', 'name' => 'Bachelor of Engineering - Electronics and Communication', 'level' => 'UG', 'duration_semesters' => 8, 'total_credits' => 178, 'is_active' => true],
            ['code' => 'BE_ME', 'name' => 'Bachelor of Engineering - Mechanical Engineering', 'level' => 'UG', 'duration_semesters' => 8, 'total_credits' => 182, 'is_active' => true],
            ['code' => 'ME_CE', 'name' => 'Master of Engineering - Computer Engineering', 'level' => 'PG', 'duration_semesters' => 4, 'total_credits' => 72, 'is_active' => true],
        ]);

        $this->updateByPosition('subjects', 'subject_id', [
            ['code' => '3160704', 'name' => 'Operating System', 'type' => 'Theory', 'subject_category' => 'Core', 'credits' => 4, 'theory_hours' => 4, 'lab_hours' => 2, 'tutorial_hours' => 0, 'is_elective' => false, 'is_active' => true],
            ['code' => '3160713', 'name' => 'Web Technology', 'type' => 'Lab', 'subject_category' => 'Core', 'credits' => 4, 'theory_hours' => 3, 'lab_hours' => 4, 'tutorial_hours' => 0, 'is_elective' => false, 'is_active' => true],
            ['code' => '3161608', 'name' => 'Data Mining', 'type' => 'Theory', 'subject_category' => 'Elective', 'credits' => 3, 'theory_hours' => 3, 'lab_hours' => 2, 'tutorial_hours' => 0, 'is_elective' => true, 'is_active' => true],
            ['code' => '3151910', 'name' => 'Design Engineering', 'type' => 'Tutorial', 'subject_category' => 'Audit', 'credits' => 2, 'theory_hours' => 1, 'lab_hours' => 0, 'tutorial_hours' => 2, 'is_elective' => false, 'is_active' => true],
            ['code' => '3170720', 'name' => 'Artificial Intelligence', 'type' => 'Theory', 'subject_category' => 'Open Elective', 'credits' => 3, 'theory_hours' => 3, 'lab_hours' => 2, 'tutorial_hours' => 0, 'is_elective' => true, 'is_active' => true],
        ]);

        $this->updateStudents();
        $this->updateStaff();
        $this->updateUsers();
    }

    private function updateStudents(): void
    {
        $students = [
            ['enrollment_no' => '230120107001', 'first_name' => 'Aarav', 'last_name' => 'Patel', 'gender' => 'Male', 'dob' => '2005-04-18', 'phone' => '9876501001', 'email' => 'aarav.patel@student.example', 'address' => 'C-401, Satellite, Ahmedabad', 'guardian_name' => 'Nilesh Patel', 'guardian_phone' => '9876501101', 'photo_url' => 'uploads/photos/demo-aarav.png', 'admission_date' => '2023-07-15', 'admission_type' => 'ACPC', 'is_active' => true],
            ['enrollment_no' => '230120107002', 'first_name' => 'Kavya', 'last_name' => 'Shah', 'gender' => 'Female', 'dob' => '2005-09-02', 'phone' => '9876501002', 'email' => 'kavya.shah@student.example', 'address' => 'B-12, Maninagar, Ahmedabad', 'guardian_name' => 'Pooja Shah', 'guardian_phone' => '9876501102', 'photo_url' => 'uploads/photos/demo-kavya.png', 'admission_date' => '2023-07-15', 'admission_type' => 'Direct', 'is_active' => true],
            ['enrollment_no' => '230120107003', 'first_name' => 'Mihir', 'last_name' => 'Mehta', 'gender' => 'Male', 'dob' => '2004-12-21', 'phone' => '9876501003', 'email' => 'mihir.mehta@student.example', 'address' => 'A-8, Vastrapur, Ahmedabad', 'guardian_name' => 'Raj Mehta', 'guardian_phone' => '9876501103', 'photo_url' => 'uploads/photos/demo-mihir.png', 'admission_date' => '2023-07-16', 'admission_type' => 'Management', 'is_active' => true],
            ['enrollment_no' => '230120107004', 'first_name' => 'Nisha', 'last_name' => 'Desai', 'gender' => 'Female', 'dob' => '2005-02-14', 'phone' => '9876501004', 'email' => 'nisha.desai@student.example', 'address' => '44, Adajan, Surat', 'guardian_name' => 'Bhavesh Desai', 'guardian_phone' => '9876501104', 'photo_url' => 'uploads/photos/demo-nisha.png', 'admission_date' => '2023-07-16', 'admission_type' => 'ACPC', 'is_active' => true],
            ['enrollment_no' => '230120107005', 'first_name' => 'Rohan', 'last_name' => 'Trivedi', 'gender' => 'Male', 'dob' => '2004-11-06', 'phone' => '9876501005', 'email' => 'rohan.trivedi@student.example', 'address' => '9, Vidyanagar, Anand', 'guardian_name' => 'Hiren Trivedi', 'guardian_phone' => '9876501105', 'photo_url' => 'uploads/photos/demo-rohan.png', 'admission_date' => '2023-07-17', 'admission_type' => 'Direct', 'is_active' => true],
        ];

        $this->updateByPosition('students', 'student_id', $students);
    }

    private function updateStaff(): void
    {
        $staff = [
            ['employee_code' => 'TCE001', 'first_name' => 'Dr. Anjali', 'last_name' => 'Joshi', 'gender' => 'Female', 'dob' => '1984-03-12', 'phone' => '9876510001', 'email' => 'anjali.joshi@college.example', 'address' => 'Faculty Quarters, Ahmedabad', 'photo_url' => 'uploads/photos/staff-anjali.png', 'staff_type' => 'Teaching', 'employment_type' => 'Permanent', 'join_date' => '2012-06-18', 'contract_end_date' => '2035-05-31', 'is_active' => true],
            ['employee_code' => 'TIT002', 'first_name' => 'Prof. Chirag', 'last_name' => 'Dave', 'gender' => 'Male', 'dob' => '1981-08-24', 'phone' => '9876510002', 'email' => 'chirag.dave@college.example', 'address' => 'Science City Road, Ahmedabad', 'photo_url' => 'uploads/photos/staff-chirag.png', 'staff_type' => 'Teaching', 'employment_type' => 'Permanent', 'join_date' => '2014-07-01', 'contract_end_date' => '2035-05-31', 'is_active' => true],
            ['employee_code' => 'TEC003', 'first_name' => 'Dr. Farah', 'last_name' => 'Shaikh', 'gender' => 'Female', 'dob' => '1986-01-09', 'phone' => '9876510003', 'email' => 'farah.shaikh@college.example', 'address' => 'Bodakdev, Ahmedabad', 'photo_url' => 'uploads/photos/staff-farah.png', 'staff_type' => 'Teaching', 'employment_type' => 'Contractual', 'join_date' => '2018-08-01', 'contract_end_date' => '2027-05-31', 'is_active' => true],
            ['employee_code' => 'ADM004', 'first_name' => 'Manish', 'last_name' => 'Parmar', 'gender' => 'Male', 'dob' => '1979-06-10', 'phone' => '9876510004', 'email' => 'manish.parmar@college.example', 'address' => 'Paldi, Ahmedabad', 'photo_url' => 'uploads/photos/staff-manish.png', 'staff_type' => 'Non-Teaching', 'employment_type' => 'Permanent', 'join_date' => '2010-04-01', 'contract_end_date' => '2032-05-31', 'is_active' => true],
            ['employee_code' => 'LAB005', 'first_name' => 'Hetal', 'last_name' => 'Vyas', 'gender' => 'Female', 'dob' => '1990-10-29', 'phone' => '9876510005', 'email' => 'hetal.vyas@college.example', 'address' => 'Gota, Ahmedabad', 'photo_url' => 'uploads/photos/staff-hetal.png', 'staff_type' => 'Non-Teaching', 'employment_type' => 'Contractual', 'join_date' => '2020-01-10', 'contract_end_date' => '2027-05-31', 'is_active' => true],
        ];

        $this->updateByPosition('staff', 'staff_id', $staff);

        $departmentIds = DB::table('departments')->orderBy('dept_id')->pluck('dept_id')->values();
        DB::table('staff')->orderBy('staff_id')->limit(self::TARGET_ROWS)->get()->values()->each(function ($member, $index) use ($departmentIds) {
            if ($departmentIds->isNotEmpty()) {
                DB::table('staff')->where('staff_id', $member->staff_id)->update(['dept_id' => $departmentIds[$index % $departmentIds->count()]]);
            }
        });

        DB::table('departments')->orderBy('dept_id')->limit(self::TARGET_ROWS)->get()->values()->each(function ($department, $index) {
            $staffId = DB::table('staff')->orderBy('staff_id')->skip($index)->value('staff_id');
            DB::table('departments')->where('dept_id', $department->dept_id)->update(['hod_staff_id' => $staffId]);
        });
    }

    private function updateUsers(): void
    {
        $roles = DB::table('user_roles')->pluck('role_id', 'role_name');
        $studentIds = DB::table('students')->orderBy('student_id')->pluck('student_id')->values();
        $staffIds = DB::table('staff')->orderBy('staff_id')->pluck('staff_id')->values();
        $collegeIds = DB::table('colleges')->orderBy('college_id')->pluck('college_id')->values();
        $universityIds = DB::table('universities')->orderBy('university_id')->pluck('university_id')->values();
        $deptIds = DB::table('departments')->orderBy('dept_id')->pluck('dept_id')->values();
        $programmeIds = DB::table('programmes')->orderBy('programme_id')->pluck('programme_id')->values();

        DB::table('users')->orderBy('user_id')->limit(self::TARGET_ROWS)->get()->values()->each(function ($user, $index) use ($roles, $studentIds, $staffIds, $collegeIds, $universityIds, $deptIds, $programmeIds) {
            $isStudent = $index >= 4;
            $referenceIds = $isStudent ? $studentIds : $staffIds;
            $referenceId = $referenceIds->isNotEmpty() ? $referenceIds[$index % $referenceIds->count()] : null;

            $roleName = match ($index) {
                0 => 'Super Admin',
                1 => 'Admin',
                2 => 'HOD',
                3 => 'Teaching Staff',
                default => 'Student',
            };

            DB::table('users')->where('user_id', $user->user_id)->update([
                'role_id' => $roles[$roleName] ?? $user->role_id,
                'university_id' => $universityIds->isNotEmpty() ? $universityIds[$index % $universityIds->count()] : null,
                'college_id' => $collegeIds->isNotEmpty() ? $collegeIds[$index % $collegeIds->count()] : null,
                'dept_id' => $deptIds->isNotEmpty() ? $deptIds[$index % $deptIds->count()] : null,
                'programme_id' => $programmeIds->isNotEmpty() ? $programmeIds[$index % $programmeIds->count()] : null,
                'reference_type' => $index === 0 ? null : ($isStudent ? 'Student' : 'Staff'),
                'reference_id' => $index === 0 ? null : $referenceId,
                'username' => ['admin', 'demo.admin', 'demo.hod', 'demo.faculty', 'demo.student'][$index] ?? 'demo.user'.$index,
                'email' => ['admin@gtu.test', 'admin@demo.example', 'hod@demo.example', 'faculty@demo.example', 'student@demo.example'][$index] ?? 'demo.user'.$index.'@example.test',
                'phone' => '987652'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'password_hash' => Hash::make($index === 0 ? 'ChangeMe123!' : 'password'),
                'is_active' => true,
                'is_verified' => true,
                'last_login' => now()->subDays($index),
            ]);
        });
    }

    private function updateByPosition(string $table, string $primaryKey, array $records): void
    {
        $columns = collect($this->columns($table))->pluck('name')->all();
        $ids = DB::table($table)->orderBy($primaryKey)->limit(count($records))->pluck($primaryKey)->values();

        foreach ($records as $index => $record) {
            if (! isset($ids[$index])) {
                continue;
            }

            $payload = collect($record)
                ->only($columns)
                ->all();

            if ($payload !== []) {
                DB::table($table)->where($primaryKey, $ids[$index])->update($payload);
            }
        }
    }

    private function primaryKey(string $table): ?string
    {
        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $key = DB::selectOne(
                'select COLUMN_NAME
                 from information_schema.KEY_COLUMN_USAGE
                 where TABLE_SCHEMA = DATABASE()
                    and TABLE_NAME = ?
                    and CONSTRAINT_NAME = ?',
                [$table, 'PRIMARY']
            );

            return $key?->COLUMN_NAME;
        }

        foreach (Schema::getColumns($table) as $column) {
            if (($column['primary'] ?? false) || ($column['auto_increment'] ?? false)) {
                return $column['name'];
            }
        }

        return null;
    }
}
