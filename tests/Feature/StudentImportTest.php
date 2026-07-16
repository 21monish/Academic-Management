<?php

use App\Models\Category;
use App\Models\College;
use App\Models\Department;
use App\Models\Programme;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use App\Models\UserRole;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

function studentImportUser(): User
{
    test()->seed(RolePermissionSeeder::class);

    $role = UserRole::query()->where('role_name', 'Super Admin')->firstOrFail();

    return User::factory()->create(['role_id' => $role->role_id]);
}

function studentImportProgramme(): Programme
{
    $university = University::query()->create(['name' => 'Import University']);
    $college = College::query()->create([
        'university_id' => $university->university_id,
        'code' => 'IMP',
        'name' => 'Import College',
    ]);
    $department = Department::query()->create([
        'college_id' => $college->college_id,
        'code' => 'CE',
        'name' => 'Computer Engineering',
    ]);

    return Programme::query()->create([
        'dept_id' => $department->dept_id,
        'code' => 'BECE',
        'name' => 'BE Computer Engineering',
        'level' => 'UG',
        'duration_semesters' => 8,
    ]);
}

function uploadedCsv(string $name, string $content): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'student-import-');
    file_put_contents($path, $content);

    return new UploadedFile($path, $name, 'text/csv', null, true);
}

test('students can be imported from csv using programme code', function () {
    $user = studentImportUser();
    studentImportProgramme();
    Category::query()->create([
        'code' => 'GEN',
        'name' => 'General',
        'is_reserved' => false,
    ]);

    $csv = implode("\n", [
        'programme_code,enrollment_no,first_name,last_name,gender,dob,phone,email,category_code,student_type,admission_type,admission_date,guardian_name,guardian_phone,address',
        'BECE,IMP25001,Asha,Patel,Female,2005-04-21,9876543210,asha.import@example.com,GEN,Regular,ACPC,2026-07-16,Ramesh Patel,9876543211,Ahmedabad',
    ]);

    $response = $this->actingAs($user)->post(route('students.import'), [
        'file' => uploadedCsv('students.csv', $csv),
    ]);

    $student = Student::query()->where('enrollment_no', 'IMP25001')->firstOrFail();
    $login = User::query()
        ->where('reference_type', 'Student')
        ->where('reference_id', $student->student_id)
        ->firstOrFail();

    $response->assertRedirect(route('students.index'));
    $response->assertSessionHas('status');
    expect($student->first_name)->toBe('Asha');
    expect($student->programme?->code)->toBe('BECE');
    expect($student->category?->code)->toBe('GEN');
    expect($login->username)->toBe('IMP25001');
    expect(Hash::check('21042005', $login->password_hash))->toBeTrue();
});

test('student import template can be downloaded', function () {
    $user = studentImportUser();

    $response = $this->actingAs($user)
        ->get(route('students.import-template'))
        ->assertOk();

    expect($response->streamedContent())
        ->toContain('programme_code')
        ->toContain('first_name');
});
