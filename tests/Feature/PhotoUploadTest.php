<?php

use App\Models\Staff;
use App\Models\Student;
use App\Models\TeachingStaff;
use App\Models\NonTeachingStaff;
use App\Models\University;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\RolePermissionSeeder;

function photoUploadLookups(): array
{
    $universityId = DB::table('universities')->insertGetId([
        'name' => 'GTU',
    ], 'university_id');

    $collegeId = DB::table('colleges')->insertGetId([
        'university_id' => $universityId,
        'code' => 'ITR',
        'name' => 'ITR College',
    ], 'college_id');

    $departmentId = DB::table('departments')->insertGetId([
        'college_id' => $collegeId,
        'code' => 'CE',
        'name' => 'Computer Engineering',
    ], 'dept_id');

    $programmeId = DB::table('programmes')->insertGetId([
        'dept_id' => $departmentId,
        'code' => 'BECE',
        'name' => 'BE Computer Engineering',
        'level' => 'UG',
    ], 'programme_id');

    return [$collegeId, $departmentId, $programmeId];
}

function staffAccountRole(string $name, string $staffType): UserRole
{
    return UserRole::query()->firstOrCreate(
        ['role_name' => $name],
        [
            'description' => "{$staffType} staff account role",
            'staff_type' => $staffType,
            'is_active' => true,
        ]
    );
}

test('student photo upload stores file path in database', function () {
    [$collegeId, , $programmeId] = photoUploadLookups();
    $role = UserRole::query()->firstOrCreate(
        ['role_name' => 'Super Admin'],
        ['description' => 'Full system access', 'is_system_role' => true]
    );
    $user = User::factory()->create(['role_id' => $role->role_id]);

    $response = $this->actingAs($user)->post(route('students.store'), [
        'college_id' => $collegeId,
        'programme_id' => $programmeId,
        'enrollment_no' => 'ENR001',
        'first_name' => 'Asha',
        'last_name' => 'Patel',
        'dob' => '2005-04-21',
        'photo' => UploadedFile::fake()->image('student.jpg', 120, 120),
        'is_active' => '1',
    ]);

    $student = Student::query()->where('enrollment_no', 'ENR001')->first();

    $response->assertRedirect(route('students.show', $student));
    expect($student->photo_url)->toStartWith('uploads/photos/');
    expect(File::exists(public_path($student->photo_url)))->toBeTrue();

    File::delete(public_path($student->photo_url));
});

test('staff photo upload stores file path in database', function () {
    [$collegeId, $departmentId] = photoUploadLookups();
    $role = UserRole::query()->firstOrCreate(
        ['role_name' => 'Super Admin'],
        ['description' => 'Full system access', 'is_system_role' => true]
    );
    $accountRole = staffAccountRole('Faculty Photo Role', 'Teaching');
    $user = User::factory()->create(['role_id' => $role->role_id]);

    $response = $this->actingAs($user)->post(route('staff.store'), [
        'college_id' => $collegeId,
        'dept_id' => $departmentId,
        'employee_code' => 'EMP001',
        'first_name' => 'Ravi',
        'last_name' => 'Shah',
        'email' => 'ravi@example.test',
        'photo' => UploadedFile::fake()->image('staff.png', 120, 120),
        'staff_type' => 'Teaching',
        'account_role_id' => $accountRole->role_id,
        'employment_type' => 'Permanent',
        'is_active' => '1',
    ]);

    $staff = Staff::query()->where('employee_code', 'EMP001')->first();

    $response->assertRedirect(route('staff.index'));
    expect($staff->photo_url)->toStartWith('uploads/photos/');
    expect(File::exists(public_path($staff->photo_url)))->toBeTrue();

    File::delete(public_path($staff->photo_url));
});

test('staff creation creates linked login account', function () {
    [$collegeId, $departmentId] = photoUploadLookups();
    $role = UserRole::query()->firstOrCreate(
        ['role_name' => 'Super Admin'],
        ['description' => 'Full system access', 'is_system_role' => true]
    );
    $accountRole = staffAccountRole('Faculty Login Role', 'Teaching');
    $user = User::factory()->create(['role_id' => $role->role_id]);

    $response = $this->actingAs($user)->post(route('staff.store'), [
        'college_id' => $collegeId,
        'dept_id' => $departmentId,
        'employee_code' => 'FACLOGIN001',
        'first_name' => 'Nisha',
        'last_name' => 'Patel',
        'dob' => '1988-04-12',
        'email' => 'nisha.faculty@example.test',
        'staff_type' => 'Teaching',
        'account_role_id' => $accountRole->role_id,
        'employment_type' => 'Permanent',
        'designation' => 'Assistant Professor',
        'is_active' => '1',
    ]);

    $staff = Staff::query()->where('employee_code', 'FACLOGIN001')->firstOrFail();
    $login = User::query()
        ->where('reference_type', 'Staff')
        ->where('reference_id', $staff->staff_id)
        ->firstOrFail();

    $response->assertRedirect(route('staff.index'));
    expect($login->username)->toBe('FACLOGIN001');
    expect($login->email)->toBe('nisha.faculty@example.test');
    expect(Hash::check('12041988', $login->password_hash))->toBeTrue();
    expect($login->role?->role_name)->toBe('Faculty Login Role');
    expect($login->college_id)->toBe($collegeId);
    expect($login->dept_id)->toBe($departmentId);
    expect($login->reference_type)->toBe('Staff');
    expect($login->reference_id)->toBe($staff->staff_id);
    expect($login->must_change_password)->toBeTrue();
});

test('accountant staff creation creates accountant login account', function () {
    [$collegeId, $departmentId] = photoUploadLookups();
    $role = UserRole::query()->firstOrCreate(
        ['role_name' => 'Super Admin'],
        ['description' => 'Full system access', 'is_system_role' => true]
    );
    $accountRole = staffAccountRole('Accounts Login Role', 'Non-Teaching');
    $user = User::factory()->create(['role_id' => $role->role_id]);

    $this->actingAs($user)->post(route('staff.store'), [
        'college_id' => $collegeId,
        'dept_id' => $departmentId,
        'employee_code' => 'ACCLOGIN001',
        'first_name' => 'Ketan',
        'last_name' => 'Mehta',
        'email' => 'ketan.accounts@example.test',
        'staff_type' => 'Non-Teaching',
        'account_role_id' => $accountRole->role_id,
        'employment_type' => 'Permanent',
        'role' => 'Accountant',
        'is_active' => '1',
    ])->assertRedirect(route('staff.index'));

    $staff = Staff::query()->where('employee_code', 'ACCLOGIN001')->firstOrFail();
    $nonTeaching = NonTeachingStaff::query()->where('staff_id', $staff->staff_id)->firstOrFail();
    $login = User::query()
        ->where('reference_type', 'Staff')
        ->where('reference_id', $staff->staff_id)
        ->firstOrFail();

    expect($nonTeaching->role)->toBe('Accountant');
    expect($login->username)->toBe('ACCLOGIN001');
    expect(Hash::check('ACCLOGIN001', $login->password_hash))->toBeTrue();
    expect($login->role?->role_name)->toBe('Accounts Login Role');
});

test('staff type update keeps only the active staff profile', function () {
    [$collegeId, $departmentId] = photoUploadLookups();
    $role = UserRole::query()->firstOrCreate(
        ['role_name' => 'Super Admin'],
        ['description' => 'Full system access', 'is_system_role' => true]
    );
    $teachingRole = staffAccountRole('Type Change Teaching Role', 'Teaching');
    $nonTeachingRole = staffAccountRole('Type Change Non Teaching Role', 'Non-Teaching');
    $user = User::factory()->create(['role_id' => $role->role_id]);

    $this->actingAs($user)->post(route('staff.store'), [
        'college_id' => $collegeId,
        'dept_id' => $departmentId,
        'employee_code' => 'TYPECHANGE001',
        'first_name' => 'Mira',
        'last_name' => 'Shah',
        'email' => 'mira.type@example.test',
        'staff_type' => 'Teaching',
        'account_role_id' => $teachingRole->role_id,
        'employment_type' => 'Permanent',
        'designation' => 'Lecturer',
        'is_active' => '1',
    ])->assertRedirect(route('staff.index'));

    $staff = Staff::query()->where('employee_code', 'TYPECHANGE001')->firstOrFail();
    expect(TeachingStaff::query()->where('staff_id', $staff->staff_id)->exists())->toBeTrue();

    $this->actingAs($user)->put(route('staff.update', $staff), [
        'college_id' => $collegeId,
        'dept_id' => $departmentId,
        'employee_code' => 'TYPECHANGE001',
        'first_name' => 'Mira',
        'last_name' => 'Shah',
        'email' => 'mira.type@example.test',
        'staff_type' => 'Non-Teaching',
        'account_role_id' => $nonTeachingRole->role_id,
        'employment_type' => 'Permanent',
        'role' => 'Clerk',
        'department_section' => 'Office',
        'is_active' => '1',
    ])->assertRedirect(route('staff.index'));

    expect(TeachingStaff::query()->where('staff_id', $staff->staff_id)->exists())->toBeFalse();
    expect(NonTeachingStaff::query()->where('staff_id', $staff->staff_id)->where('role', 'Clerk')->exists())->toBeTrue();
});

test('both staff type creates teaching and non teaching profiles', function () {
    [$collegeId, $departmentId] = photoUploadLookups();
    $role = UserRole::query()->firstOrCreate(
        ['role_name' => 'Super Admin'],
        ['description' => 'Full system access', 'is_system_role' => true]
    );
    $accountRole = staffAccountRole('Both Staff Login Role', 'Both');
    $user = User::factory()->create(['role_id' => $role->role_id]);

    $this->actingAs($user)->post(route('staff.store'), [
        'college_id' => $collegeId,
        'dept_id' => $departmentId,
        'employee_code' => 'BOTHLOGIN001',
        'first_name' => 'Dev',
        'last_name' => 'Trivedi',
        'email' => 'dev.both@example.test',
        'staff_type' => 'Both',
        'account_role_id' => $accountRole->role_id,
        'employment_type' => 'Permanent',
        'designation' => 'Lecturer',
        'qualification' => 'M.Tech',
        'department_section' => 'Administration',
        'is_active' => '1',
    ])->assertRedirect(route('staff.index'));

    $staff = Staff::query()->where('employee_code', 'BOTHLOGIN001')->firstOrFail();

    expect($staff->staff_type)->toBe('Both');
    expect(TeachingStaff::query()->where('staff_id', $staff->staff_id)->where('designation', 'Lecturer')->exists())->toBeTrue();
    expect(NonTeachingStaff::query()->where('staff_id', $staff->staff_id)->where('department_section', 'Administration')->exists())->toBeTrue();
});

test('university logo upload stores file path in database', function () {
    $role = UserRole::query()->firstOrCreate(
        ['role_name' => 'Super Admin'],
        ['description' => 'Full system access', 'is_system_role' => true]
    );
    $user = User::factory()->create(['role_id' => $role->role_id]);

    $response = $this->actingAs($user)->post(route('universities.store'), [
        'name' => 'Logo Test University',
        'email' => 'logo-university@example.test',
        'website' => 'https://logo-university.example.test',
        'logo' => UploadedFile::fake()->image('logo.png', 120, 120),
    ]);

    $university = University::query()->where('name', 'Logo Test University')->firstOrFail();

    $response->assertRedirect(route('universities.index'));
    expect($university->logo_url)->toStartWith('uploads/logos/');
    expect(File::exists(public_path($university->logo_url)))->toBeTrue();

    File::delete(public_path($university->logo_url));
});

test('student creation creates login from enrollment number and dob', function () {
    [$collegeId, , $programmeId] = photoUploadLookups();
    $this->seed(RolePermissionSeeder::class);
    $role = UserRole::query()->firstOrCreate(
        ['role_name' => 'Super Admin'],
        ['description' => 'Full system access', 'is_system_role' => true]
    );
    $user = User::factory()->create(['role_id' => $role->role_id]);

    $response = $this->actingAs($user)->post(route('students.store'), [
        'college_id' => $collegeId,
        'programme_id' => $programmeId,
        'enrollment_no' => 'ENRLOGIN001',
        'first_name' => 'Asha',
        'last_name' => 'Patel',
        'dob' => '2005-04-21',
        'is_active' => '1',
    ]);

    $student = Student::query()->where('enrollment_no', 'ENRLOGIN001')->firstOrFail();
    $login = User::query()
        ->where('reference_type', 'Student')
        ->where('reference_id', $student->student_id)
        ->firstOrFail();

    $response->assertRedirect(route('students.show', $student));
    expect($login->username)->toBe('ENRLOGIN001');
    expect(Hash::check('21042005', $login->password_hash))->toBeTrue();
    expect($login->role?->role_name)->toBe('Student');
    expect($login->college_id)->toBe($collegeId);
    expect($login->programme_id)->toBe($programmeId);
    expect($login->must_change_password)->toBeTrue();
});

test('student profile remains the owner of its linked login account', function () {
    [$collegeId, , $programmeId] = photoUploadLookups();
    $this->seed(RolePermissionSeeder::class);

    $superAdmin = UserRole::query()->where('role_name', 'Super Admin')->firstOrFail();
    $manager = User::factory()->create(['role_id' => $superAdmin->role_id]);
    $unrelatedUser = User::factory()->create();

    $this->actingAs($manager)->post(route('students.store'), [
        'college_id' => $collegeId,
        'programme_id' => $programmeId,
        'enrollment_no' => 'ENRPROFILE001',
        'first_name' => 'Asha',
        'last_name' => 'Patel',
        'dob' => '2005-04-21',
        'is_active' => '1',
    ])->assertRedirect();

    $student = Student::query()->where('enrollment_no', 'ENRPROFILE001')->firstOrFail();
    $linkedUser = $student->userAccount()->firstOrFail();

    $this->actingAs($manager)->put(route('students.update', $student), [
        'college_id' => $collegeId,
        'programme_id' => $programmeId,
        'enrollment_no' => 'ENRPROFILE002',
        'first_name' => 'Asha',
        'last_name' => 'Patel',
        'dob' => '2005-04-21',
        'user_id' => $unrelatedUser->user_id,
        'is_active' => '1',
    ])->assertRedirect(route('students.show', $student));

    expect($linkedUser->fresh()->username)->toBe('ENRPROFILE002');
    expect($unrelatedUser->fresh()->reference_type)->toBeNull();

    $this->actingAs($manager)
        ->patch(route('students.deactivate', $student))
        ->assertRedirect(route('students.index'));

    expect($linkedUser->fresh()->is_active)->toBeFalse();

    $this->actingAs($manager)
        ->delete(route('students.destroy', $student))
        ->assertRedirect(route('students.index'));

    expect(User::query()->whereKey($linkedUser->user_id)->exists())->toBeFalse();
});
