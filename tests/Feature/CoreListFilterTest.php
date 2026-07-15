<?php

use App\Models\College;
use App\Models\Department;
use App\Models\Programme;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\University;
use App\Models\User;
use App\Models\UserRole;
use Database\Seeders\RolePermissionSeeder;

function coreListAdmin(): User
{
    $role = UserRole::query()->where('role_name', 'Super Admin')->firstOrFail();

    return User::factory()->create(['role_id' => $role->role_id]);
}

function coreListFixtures(): array
{
    $alphaUniversity = University::query()->create([
        'name' => 'Alpha Technical University',
        'email' => 'alpha-university@example.test',
        'phone' => '1111111111',
        'website' => 'https://alpha.example.test',
    ]);
    $betaUniversity = University::query()->create([
        'name' => 'Beta Research University',
        'email' => 'beta-university@example.test',
        'phone' => '2222222222',
        'website' => 'https://beta.example.test',
    ]);

    $alphaCollege = College::query()->create([
        'university_id' => $alphaUniversity->university_id,
        'code' => 'ALPCOL',
        'name' => 'Alpha Engineering College',
        'email' => 'alpha-college@example.test',
        'affiliation_type' => 'Autonomous',
        'college_type' => 'Government',
        'is_active' => true,
    ]);
    $betaCollege = College::query()->create([
        'university_id' => $betaUniversity->university_id,
        'code' => 'BETCOL',
        'name' => 'Beta Commerce College',
        'email' => 'beta-college@example.test',
        'affiliation_type' => 'Affiliated',
        'college_type' => 'Private',
        'is_active' => false,
    ]);

    $alphaDepartment = Department::query()->create([
        'college_id' => $alphaCollege->college_id,
        'code' => 'AIML',
        'name' => 'Artificial Intelligence Department',
        'description' => 'Visible AI department',
        'is_active' => true,
    ]);
    $betaDepartment = Department::query()->create([
        'college_id' => $betaCollege->college_id,
        'code' => 'FIN',
        'name' => 'Finance Department',
        'description' => 'Hidden finance department',
        'is_active' => false,
    ]);

    $alphaProgramme = Programme::query()->create([
        'dept_id' => $alphaDepartment->dept_id,
        'code' => 'BTECHAI',
        'name' => 'BTech Artificial Intelligence',
        'level' => 'UG',
        'duration_semesters' => 8,
        'total_credits' => 180,
        'is_active' => true,
    ]);
    $betaProgramme = Programme::query()->create([
        'dept_id' => $betaDepartment->dept_id,
        'code' => 'MBEFIN',
        'name' => 'MBA Finance',
        'level' => 'PG',
        'duration_semesters' => 4,
        'total_credits' => 90,
        'is_active' => false,
    ]);

    $alphaSemester = Semester::query()->create([
        'programme_id' => $alphaProgramme->programme_id,
        'name' => 'AI Semester One',
        'semester_no' => 1,
        'academic_year' => '2026-27',
        'is_current' => true,
        'is_active' => true,
    ]);
    $betaSemester = Semester::query()->create([
        'programme_id' => $betaProgramme->programme_id,
        'name' => 'Finance Semester Two',
        'semester_no' => 2,
        'academic_year' => '2026-27',
        'is_current' => false,
        'is_active' => false,
    ]);

    $alphaSubject = Subject::query()->create([
        'dept_id' => $alphaDepartment->dept_id,
        'code' => 'AI101',
        'name' => 'Machine Learning Basics',
        'short_name' => 'MLB',
        'type' => 'Theory',
        'subject_category' => 'Core',
        'credits' => 4,
        'is_active' => true,
    ]);
    $betaSubject = Subject::query()->create([
        'dept_id' => $betaDepartment->dept_id,
        'code' => 'FIN201',
        'name' => 'Corporate Finance',
        'short_name' => 'CF',
        'type' => 'Lab',
        'subject_category' => 'Elective',
        'credits' => 3,
        'is_active' => false,
    ]);

    $alphaRole = UserRole::query()->create([
        'role_name' => 'Alpha Operator',
        'description' => 'Visible role for filters',
        'is_active' => true,
    ]);
    $betaRole = UserRole::query()->create([
        'role_name' => 'Beta Operator',
        'description' => 'Hidden role for filters',
        'is_active' => true,
    ]);

    User::factory()->create([
        'role_id' => $alphaRole->role_id,
        'username' => 'alpha.operator',
        'email' => 'alpha.operator@example.test',
        'is_active' => true,
    ]);
    User::factory()->create([
        'role_id' => $betaRole->role_id,
        'username' => 'beta.operator',
        'email' => 'beta.operator@example.test',
        'is_active' => false,
    ]);

    return compact(
        'alphaUniversity',
        'betaUniversity',
        'alphaCollege',
        'betaCollege',
        'alphaDepartment',
        'betaDepartment',
        'alphaProgramme',
        'betaProgramme',
        'alphaSemester',
        'betaSemester',
        'alphaSubject',
        'betaSubject',
        'alphaRole',
        'betaRole',
    );
}

test('core setup list pages search filter and render matching table rows', function () {
    $this->seed(RolePermissionSeeder::class);
    $admin = coreListAdmin();
    $data = coreListFixtures();

    $this->actingAs($admin)
        ->get(route('universities.index', ['q' => 'Alpha']))
        ->assertOk()
        ->assertSee($data['alphaUniversity']->name)
        ->assertDontSee($data['betaUniversity']->name);

    $this->actingAs($admin)
        ->get(route('colleges.index', [
            'q' => 'Alpha',
            'university_id' => $data['alphaUniversity']->university_id,
            'affiliation_type' => 'Autonomous',
            'college_type' => 'Government',
            'is_active' => '1',
        ]))
        ->assertOk()
        ->assertSee($data['alphaCollege']->name)
        ->assertDontSee($data['betaCollege']->name);

    $this->actingAs($admin)
        ->get(route('departments.index', [
            'q' => 'Artificial',
            'university_id' => $data['alphaUniversity']->university_id,
            'college_id' => $data['alphaCollege']->college_id,
            'is_active' => '1',
        ]))
        ->assertOk()
        ->assertSee($data['alphaDepartment']->name)
        ->assertDontSee($data['betaDepartment']->name);

    $this->actingAs($admin)
        ->get(route('academic.programmes.index', [
            'q' => 'Artificial',
            'dept_id' => $data['alphaDepartment']->dept_id,
            'level' => 'UG',
            'is_active' => '1',
            'sort' => 'name',
            'direction' => 'asc',
        ]))
        ->assertOk()
        ->assertSee($data['alphaProgramme']->name)
        ->assertDontSee($data['betaProgramme']->name);

    $this->actingAs($admin)
        ->get(route('academic.semesters.index', [
            'search' => 'AI Semester',
            'programme_id' => $data['alphaProgramme']->programme_id,
            'is_active' => '1',
            'sort_by' => 'name',
            'sort_direction' => 'asc',
        ]))
        ->assertOk()
        ->assertSee($data['alphaSemester']->name)
        ->assertDontSee($data['betaSemester']->name);

    $this->actingAs($admin)
        ->get(route('academic.subjects.index', [
            'q' => 'Machine',
            'dept_id' => $data['alphaDepartment']->dept_id,
            'type' => 'Theory',
            'is_active' => '1',
            'sort' => 'name',
            'direction' => 'asc',
        ]))
        ->assertOk()
        ->assertSee($data['alphaSubject']->name)
        ->assertDontSee($data['betaSubject']->name);

    $this->actingAs($admin)
        ->get(route('users.index', [
            'search' => 'alpha.operator',
            'role_id' => $data['alphaRole']->role_id,
            'status' => '1',
        ]))
        ->assertOk()
        ->assertSee('alpha.operator')
        ->assertDontSee('beta.operator');

    $this->actingAs($admin)
        ->get(route('roles.index'))
        ->assertOk()
        ->assertSee('Alpha Operator')
        ->assertSee('Role')
        ->assertSee('Users');
});
