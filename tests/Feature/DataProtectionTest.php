<?php

use App\Models\College;
use App\Models\Department;
use App\Models\ApprovalRequest;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\FeeCategory;
use App\Models\FeeConcession;
use App\Models\FeeStructure;
use App\Models\Permission;
use App\Models\PermissionAudit;
use App\Models\Programme;
use App\Models\Result;
use App\Models\Semester;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentFeeLedger;
use App\Models\Subject;
use App\Models\University;
use App\Models\User;
use App\Models\UserRole;
use Database\Seeders\RolePermissionSeeder;

function protectionFixtures(): array
{
    $ownUniversity = University::create(['name' => 'Protection Own University']);
    $otherUniversity = University::create(['name' => 'Protection Other University']);

    $ownCollege = College::create([
        'university_id' => $ownUniversity->university_id,
        'code' => 'PROWN',
        'name' => 'Protection Own College',
        'is_active' => true,
    ]);

    $otherCollege = College::create([
        'university_id' => $otherUniversity->university_id,
        'code' => 'PROTH',
        'name' => 'Protection Other College',
        'is_active' => true,
    ]);

    $ownDepartment = Department::create([
        'college_id' => $ownCollege->college_id,
        'code' => 'PRCE',
        'name' => 'Protection Computer Department',
        'is_active' => true,
    ]);

    $otherDepartment = Department::create([
        'college_id' => $otherCollege->college_id,
        'code' => 'PRME',
        'name' => 'Protection Mechanical Department',
        'is_active' => true,
    ]);

    $ownProgramme = Programme::create([
        'dept_id' => $ownDepartment->dept_id,
        'code' => 'PRBECE',
        'name' => 'Protection BE Computer',
        'level' => 'UG',
        'is_active' => true,
    ]);

    $otherProgramme = Programme::create([
        'dept_id' => $otherDepartment->dept_id,
        'code' => 'PRBEME',
        'name' => 'Protection BE Mechanical',
        'level' => 'UG',
        'is_active' => true,
    ]);

    $ownSemester = Semester::create([
        'programme_id' => $ownProgramme->programme_id,
        'name' => 'Sem 1',
        'semester_no' => 1,
        'academic_year' => '2026-27',
        'is_current' => true,
        'is_active' => true,
    ]);

    return compact(
        'ownUniversity',
        'otherUniversity',
        'ownCollege',
        'otherCollege',
        'ownDepartment',
        'otherDepartment',
        'ownProgramme',
        'otherProgramme',
        'ownSemester'
    );
}

function protectionAdmin(array $attributes): User
{
    $role = UserRole::where('role_name', 'Admin')->firstOrFail();

    return User::factory()->create([
        'role_id' => $role->role_id,
        ...$attributes,
    ]);
}

test('student creation ignores tampered college and uses programme college', function () {
    $this->seed(RolePermissionSeeder::class);
    $fixtures = protectionFixtures();

    $manager = protectionAdmin([
        'university_id' => $fixtures['ownUniversity']->university_id,
        'college_id' => $fixtures['ownCollege']->college_id,
    ]);

    $this->actingAs($manager)
        ->post(route('students.store'), [
            'college_id' => $fixtures['otherCollege']->college_id,
            'programme_id' => $fixtures['ownProgramme']->programme_id,
            'first_name' => 'Locked',
            'last_name' => 'Student',
            'dob' => '2005-04-21',
            'student_type' => 'Regular',
            'is_active' => '1',
        ])
        ->assertRedirect();

    $student = Student::where('first_name', 'Locked')->firstOrFail();

    expect($student->college_id)->toBe($fixtures['ownCollege']->college_id)
        ->and($student->programme_id)->toBe($fixtures['ownProgramme']->programme_id);
});

test('college scoped manager cannot create student in another college programme', function () {
    $this->seed(RolePermissionSeeder::class);
    $fixtures = protectionFixtures();

    $manager = protectionAdmin([
        'university_id' => $fixtures['ownUniversity']->university_id,
        'college_id' => $fixtures['ownCollege']->college_id,
    ]);

    $this->actingAs($manager)
        ->post(route('students.store'), [
            'college_id' => $fixtures['ownCollege']->college_id,
            'programme_id' => $fixtures['otherProgramme']->programme_id,
            'first_name' => 'Outside',
            'last_name' => 'Student',
            'dob' => '2005-04-21',
            'student_type' => 'Regular',
            'is_active' => '1',
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('students', [
        'first_name' => 'Outside',
    ]);
});

test('staff creation ignores tampered college for college scoped manager', function () {
    $this->seed(RolePermissionSeeder::class);
    $fixtures = protectionFixtures();

    $manager = protectionAdmin([
        'university_id' => $fixtures['ownUniversity']->university_id,
        'college_id' => $fixtures['ownCollege']->college_id,
    ]);

    $staffRole = UserRole::create([
        'university_id' => $fixtures['ownUniversity']->university_id,
        'role_name' => 'Protection Teaching Staff',
        'staff_type' => 'Teaching',
        'is_active' => true,
    ]);

    $this->actingAs($manager)
        ->post(route('staff.store'), [
            'college_id' => $fixtures['otherCollege']->college_id,
            'employee_code' => 'PRSTAFF001',
            'first_name' => 'Locked',
            'last_name' => 'Staff',
            'email' => 'locked.staff@example.test',
            'staff_type' => 'Teaching',
            'account_role_id' => $staffRole->role_id,
            'employment_type' => 'Permanent',
            'staff_role' => 'Lecturer',
            'is_active' => '1',
        ])
        ->assertRedirect(route('staff.index'));

    $staff = Staff::where('employee_code', 'PRSTAFF001')->firstOrFail();

    expect($staff->college_id)->toBe($fixtures['ownCollege']->college_id);
});

test('subject creation ignores tampered department and uses selected programme department', function () {
    $this->seed(RolePermissionSeeder::class);
    $fixtures = protectionFixtures();

    $manager = protectionAdmin([
        'university_id' => $fixtures['ownUniversity']->university_id,
        'college_id' => $fixtures['ownCollege']->college_id,
    ]);

    $this->actingAs($manager)
        ->post(route('academic.subjects.store'), [
            'department_id' => $fixtures['otherDepartment']->dept_id,
            'programme_id' => $fixtures['ownProgramme']->programme_id,
            'semester_id' => $fixtures['ownSemester']->semester_id,
            'code' => 'PRSUB101',
            'name' => 'Protected Subject',
            'type' => 'Theory',
            'category' => 'Core',
            'credits' => 4,
        ])
        ->assertRedirect(route('academic.subjects.index'));

    $subject = Subject::where('code', 'PRSUB101')->firstOrFail();

    expect($subject->dept_id)->toBe($fixtures['ownDepartment']->dept_id);
});

test('department delete is blocked when programmes exist', function () {
    $this->seed(RolePermissionSeeder::class);
    $fixtures = protectionFixtures();

    $manager = protectionAdmin([
        'university_id' => $fixtures['ownUniversity']->university_id,
    ]);

    $this->actingAs($manager)
        ->delete(route('departments.destroy', $fixtures['ownDepartment']))
        ->assertSessionHasErrors('delete');

    $this->assertDatabaseHas('departments', [
        'dept_id' => $fixtures['ownDepartment']->dept_id,
    ]);
});

test('user cannot deactivate own account from user edit', function () {
    $this->seed(RolePermissionSeeder::class);
    $fixtures = protectionFixtures();

    $manager = protectionAdmin([
        'university_id' => $fixtures['ownUniversity']->university_id,
    ]);

    $this->actingAs($manager)
        ->put(route('users.update', $manager), [
            'role_id' => $manager->role_id,
            'university_id' => $fixtures['ownUniversity']->university_id,
            'username' => $manager->username,
            'email' => $manager->email,
            'password' => '',
            'password_confirmation' => '',
            'is_active' => '0',
            'is_verified' => '1',
            'must_change_password' => '0',
            'permissions' => $manager->permissions()->pluck('permissions.permission_id')->all(),
        ])
        ->assertSessionHasErrors('is_active');

    expect($manager->fresh()->is_active)->toBeTrue();
});

test('user cannot remove own permission updater access', function () {
    $this->seed(RolePermissionSeeder::class);
    $fixtures = protectionFixtures();

    $manager = protectionAdmin([
        'university_id' => $fixtures['ownUniversity']->university_id,
    ]);

    $keptPermissionIds = $manager->permissions()
        ->where(function ($query) {
            $query->where('module_name', '!=', 'user_permission')
                ->orWhere('action', 'view');
        })
        ->pluck('permissions.permission_id')
        ->all();

    $this->actingAs($manager)
        ->patch(route('users.permissions.update', $manager), [
            'permissions' => $keptPermissionIds,
        ])
        ->assertSessionHasErrors('permissions');

    expect($manager->fresh()->permissions()
        ->where('module_name', 'user_permission')
        ->where('action', 'update')
        ->exists())->toBeTrue();
});

test('permission changes are audited with actor and target user', function () {
    $this->seed(RolePermissionSeeder::class);
    $fixtures = protectionFixtures();

    $manager = protectionAdmin([
        'university_id' => $fixtures['ownUniversity']->university_id,
    ]);
    $target = User::factory()->create([
        'university_id' => $fixtures['ownUniversity']->university_id,
    ]);
    $permission = Permission::where('module_name', 'student')
        ->where('action', 'view')
        ->firstOrFail();

    $this->actingAs($manager)
        ->patch(route('users.permissions.update', $target), [
            'permissions' => [$permission->permission_id],
        ])
        ->assertRedirect(route('users.permissions.edit', $target));

    $this->assertDatabaseHas('permission_audits', [
        'actor_user_id' => $manager->user_id,
        'target_user_id' => $target->user_id,
        'permission_id' => $permission->permission_id,
        'action' => 'granted',
        'context' => 'permission_update',
    ]);

    $this->actingAs($manager)
        ->patch(route('users.permissions.update', $target), [
            'permissions' => [],
        ])
        ->assertRedirect(route('users.permissions.edit', $target));

    $this->assertDatabaseHas('permission_audits', [
        'actor_user_id' => $manager->user_id,
        'target_user_id' => $target->user_id,
        'permission_id' => $permission->permission_id,
        'action' => 'revoked',
        'context' => 'permission_update',
    ]);

    expect(PermissionAudit::where('target_user_id', $target->user_id)->count())->toBe(2);
});

test('college scoped user delete requires higher approval', function () {
    $this->seed(RolePermissionSeeder::class);
    $fixtures = protectionFixtures();

    $requester = protectionAdmin([
        'university_id' => $fixtures['ownUniversity']->university_id,
        'college_id' => $fixtures['ownCollege']->college_id,
    ]);
    $approver = protectionAdmin([
        'university_id' => $fixtures['ownUniversity']->university_id,
    ]);
    $target = User::factory()->create([
        'university_id' => $fixtures['ownUniversity']->university_id,
        'college_id' => $fixtures['ownCollege']->college_id,
    ]);

    $this->actingAs($requester)
        ->delete(route('users.destroy', $target))
        ->assertRedirect(route('users.index'));

    expect(User::whereKey($target->user_id)->exists())->toBeTrue();

    $approval = ApprovalRequest::where('action', 'delete_user')
        ->where('subject_id', $target->user_id)
        ->firstOrFail();

    $this->actingAs($requester)
        ->patch(route('approvals.approve', $approval))
        ->assertSessionHasErrors('approval');

    $this->actingAs($approver)
        ->patch(route('approvals.approve', $approval))
        ->assertRedirect();

    expect(User::whereKey($target->user_id)->exists())->toBeFalse()
        ->and($approval->fresh()->status)->toBe(ApprovalRequest::STATUS_APPROVED)
        ->and($approval->fresh()->approved_by)->toBe($approver->user_id);
});

test('college scoped student delete requires higher approval', function () {
    $this->seed(RolePermissionSeeder::class);
    $fixtures = protectionFixtures();

    $requester = protectionAdmin([
        'university_id' => $fixtures['ownUniversity']->university_id,
        'college_id' => $fixtures['ownCollege']->college_id,
    ]);
    $approver = protectionAdmin([
        'university_id' => $fixtures['ownUniversity']->university_id,
    ]);
    $student = Student::create([
        'college_id' => $fixtures['ownCollege']->college_id,
        'programme_id' => $fixtures['ownProgramme']->programme_id,
        'enrollment_no' => 'APPROVALSTU001',
        'first_name' => 'Approval',
        'last_name' => 'Student',
        'dob' => '2005-04-21',
        'is_active' => true,
    ]);
    $linkedUser = User::factory()->create([
        'university_id' => $fixtures['ownUniversity']->university_id,
        'college_id' => $fixtures['ownCollege']->college_id,
        'programme_id' => $fixtures['ownProgramme']->programme_id,
        'reference_type' => 'Student',
        'reference_id' => $student->student_id,
    ]);

    $this->actingAs($requester)
        ->delete(route('students.destroy', $student))
        ->assertRedirect(route('students.index'));

    expect(Student::whereKey($student->student_id)->exists())->toBeTrue()
        ->and(User::whereKey($linkedUser->user_id)->exists())->toBeTrue();

    $approval = ApprovalRequest::where('action', 'delete_student')
        ->where('subject_id', $student->student_id)
        ->firstOrFail();

    $this->actingAs($approver)
        ->patch(route('approvals.approve', $approval))
        ->assertRedirect();

    expect(Student::whereKey($student->student_id)->exists())->toBeFalse()
        ->and(User::whereKey($linkedUser->user_id)->exists())->toBeFalse()
        ->and($approval->fresh()->status)->toBe(ApprovalRequest::STATUS_APPROVED);
});

test('fee concession is inactive until higher approval', function () {
    $this->seed(RolePermissionSeeder::class);
    $fixtures = protectionFixtures();

    $requester = protectionAdmin([
        'university_id' => $fixtures['ownUniversity']->university_id,
        'college_id' => $fixtures['ownCollege']->college_id,
    ]);
    $approver = protectionAdmin([
        'university_id' => $fixtures['ownUniversity']->university_id,
    ]);
    $student = Student::create([
        'college_id' => $fixtures['ownCollege']->college_id,
        'programme_id' => $fixtures['ownProgramme']->programme_id,
        'enrollment_no' => 'FEEAPPROVAL001',
        'first_name' => 'Fee',
        'last_name' => 'Student',
        'dob' => '2005-04-21',
        'is_active' => true,
    ]);
    $year = AcademicYear::create([
        'college_id' => $fixtures['ownCollege']->college_id,
        'label' => '2026-27',
        'status' => 'Active',
        'is_current' => true,
    ]);
    $feeCategory = FeeCategory::create([
        'name' => 'Tuition Approval Fee',
        'fee_type' => 'Academic',
        'is_active' => true,
    ]);
    $structure = FeeStructure::create([
        'college_id' => $fixtures['ownCollege']->college_id,
        'programme_id' => $fixtures['ownProgramme']->programme_id,
        'academic_year_id' => $year->academic_year_id,
        'semester_id' => $fixtures['ownSemester']->semester_id,
        'fee_category_id' => $feeCategory->fee_category_id,
        'amount' => 1000,
        'is_active' => true,
    ]);
    $ledger = StudentFeeLedger::create([
        'student_id' => $student->student_id,
        'fee_structure_id' => $structure->fee_structure_id,
        'academic_year_id' => $year->academic_year_id,
        'semester_id' => $fixtures['ownSemester']->semester_id,
        'total_amount' => 1000,
        'net_payable' => 1000,
        'balance_due' => 1000,
    ]);

    $this->actingAs($requester)
        ->post(route('fees.concessions.store'), [
            'student_id' => $student->student_id,
            'ledger_id' => $ledger->ledger_id,
            'concession_type' => 'Merit',
            'concession_amount' => 100,
            'is_active' => '1',
        ])
        ->assertRedirect();

    $concession = FeeConcession::where('student_id', $student->student_id)->firstOrFail();
    $approval = ApprovalRequest::where('action', 'fee_concession')
        ->where('subject_id', $concession->concession_id)
        ->firstOrFail();

    expect($concession->is_active)->toBeFalsy()
        ->and($concession->approved_by)->toBeNull()
        ->and((float) $ledger->fresh()->concession_amount)->toBe(0.0);

    $this->actingAs($approver)
        ->patch(route('approvals.approve', $approval))
        ->assertRedirect();

    expect($concession->fresh()->is_active)->toBeTruthy()
        ->and($concession->fresh()->approved_by)->toBe($approver->user_id)
        ->and((float) $ledger->fresh()->concession_amount)->toBe(100.0)
        ->and((float) $ledger->fresh()->net_payable)->toBe(900.0);
});

test('result publish automation creates approval request for lower user', function () {
    $this->seed(RolePermissionSeeder::class);
    $fixtures = protectionFixtures();

    $requester = protectionAdmin([
        'university_id' => $fixtures['ownUniversity']->university_id,
        'college_id' => $fixtures['ownCollege']->college_id,
    ]);
    $approver = protectionAdmin([
        'university_id' => $fixtures['ownUniversity']->university_id,
    ]);
    $student = Student::create([
        'college_id' => $fixtures['ownCollege']->college_id,
        'programme_id' => $fixtures['ownProgramme']->programme_id,
        'enrollment_no' => 'RESULTAPPROVAL001',
        'first_name' => 'Result',
        'last_name' => 'Student',
        'dob' => '2005-04-21',
        'is_active' => true,
    ]);
    $year = AcademicYear::create([
        'college_id' => $fixtures['ownCollege']->college_id,
        'label' => '2027-28',
        'status' => 'Active',
        'is_current' => true,
    ]);
    $enrollment = StudentEnrollment::create([
        'student_id' => $student->student_id,
        'semester_id' => $fixtures['ownSemester']->semester_id,
        'academic_year_id' => $year->academic_year_id,
        'status' => 'Active',
    ]);
    $subject = Subject::create([
        'dept_id' => $fixtures['ownDepartment']->dept_id,
        'code' => 'APPRES101',
        'name' => 'Approval Result Subject',
        'type' => 'Theory',
        'subject_category' => 'Core',
        'is_active' => true,
    ]);
    $exam = Exam::create([
        'academic_year_id' => $year->academic_year_id,
        'semester_id' => $fixtures['ownSemester']->semester_id,
        'college_id' => $fixtures['ownCollege']->college_id,
        'exam_name' => 'Approval Result Exam',
        'exam_type' => 'EndSem',
    ]);
    $examSubject = ExamSubject::create([
        'exam_id' => $exam->exam_id,
        'subject_id' => $subject->subject_id,
    ]);
    $result = Result::create([
        'student_id' => $student->student_id,
        'exam_subject_id' => $examSubject->exam_subject_id,
        'enrollment_id' => $enrollment->enrollment_id,
        'total_marks' => 70,
        'result_status' => 'Pass',
        'is_published' => false,
    ]);

    $this->actingAs($requester)
        ->post(route('automations.run', 'results'))
        ->assertRedirect();

    expect($result->fresh()->is_published)->toBeFalse();

    $approval = ApprovalRequest::where('action', 'publish_result')
        ->where('subject_id', $result->result_id)
        ->firstOrFail();

    $this->actingAs($approver)
        ->patch(route('approvals.approve', $approval))
        ->assertRedirect();

    expect($result->fresh()->is_published)->toBeTrue()
        ->and($approval->fresh()->status)->toBe(ApprovalRequest::STATUS_APPROVED);
});
