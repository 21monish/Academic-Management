<?php

use App\Models\User;
use App\Models\UserRole;

function superAdminUser(): User
{
    $role = UserRole::query()->firstOrCreate(
        ['role_name' => 'Super Admin'],
        ['description' => 'Full system access', 'is_system_role' => true, 'is_active' => true]
    );

    return User::factory()->create(['role_id' => $role->role_id]);
}

test('core authenticated module pages render', function () {
    $user = superAdminUser();

    $routes = [
        'dashboard',
        'universities.index',
        'colleges.index',
        'departments.index',
        'users.index',
        'roles.index',
        'staff.index',
        'students.index',
        'academic.categories.index',
        'academic.academic-years.index',
        'academic.programmes.index',
        'academic.semesters.index',
        'academic.subjects.index',
        'academic.curriculum.index',
        'academic.elective-groups.index',
        'attendance.assignments',
        'attendance.slots',
        'attendance.lectures',
        'attendance.summaries',
        'attendance.defaulters',
        'exams.index',
        'exams.subjects',
        'exams.grades',
        'exams.marks',
        'exams.results',
        'exams.backlogs',
        'exams.promotions',
        'exams.logistics.configs',
        'exams.logistics.tickets',
        'exams.logistics.rooms',
        'exams.logistics.seating',
        'exams.logistics.invigilators',
        'exams.logistics.practical-schedules',
        'exams.logistics.practical-batches',
        'exams.logistics.practical-marks',
        'fees.categories',
        'fees.structures',
        'fees.ledgers',
        'fees.collections',
        'fees.receipts',
        'fees.concessions',
        'fees.scholarships',
        'fees.reports',
        'leave.types',
        'leave.balances',
        'leave.applications',
        'leave.approvals',
        'leave.cancellations',
        'leave.substitutes',
        'leave.holidays',
        'notices.categories',
        'notices.index',
        'notices.audiences',
        'notices.attachments',
        'notices.acknowledgements',
        'reports.students',
        'reports.attendance',
        'reports.results',
        'reports.fee-receipts',
        'reports.hall-tickets',
        'reports.staff',
        'reports.activity',
        'system.settings',
        'system.health',
    ];

    foreach ($routes as $route) {
        $this->actingAs($user)
            ->get(route($route))
            ->assertOk();
    }
});
