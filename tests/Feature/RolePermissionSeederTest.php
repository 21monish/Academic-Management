<?php

use App\Models\Permission;
use App\Models\User;
use App\Models\UserRole;
use App\Http\Controllers\UserManagement\UserController;
use App\Services\AccessScopeService;
use App\Services\DataIntegrityService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

test('default roles receive expected permissions', function () {
    $this->seed(RolePermissionSeeder::class);

    $superAdmin = UserRole::where('role_name', 'Super Admin')->first();
    $admin = UserRole::where('role_name', 'Admin')->first();
    $student = UserRole::where('role_name', 'Student')->first();

    expect($superAdmin)->not->toBeNull();
    expect($admin)->not->toBeNull();
    expect($student)->not->toBeNull();
    expect(UserRole::where('is_system_role', true)->pluck('role_name')->sort()->values()->all())
        ->toBe(['Admin', 'Student', 'Super Admin']);
    expect($superAdmin->permissions()->count())->toBe(Permission::count());
    expect($admin->permissions()->where('module_name', 'user_permission')->where('action', 'update')->exists())->toBeTrue();
    expect($admin->permissions()->where('module_name', 'certificate')->where('action', 'view')->exists())->toBeTrue();
    expect($admin->permissions()->where('module_name', 'certificate')->where('action', 'generate')->exists())->toBeTrue();
    expect($admin->permissions()->where('module_name', 'system_health')->where('action', 'view')->exists())->toBeTrue();
    expect($admin->permissions()->where('module_name', 'approval_request')->where('action', 'view')->exists())->toBeTrue();
    expect($admin->permissions()->where('module_name', 'approval_request')->where('action', 'approve')->exists())->toBeTrue();
    expect($admin->permissions()->where('module_name', 'license_plan')->exists())->toBeFalse();
    expect($student->permissions()->where('module_name', 'notice')->where('action', 'view')->exists())->toBeTrue();
    expect($student->permissions()->where('module_name', 'attendance_summary')->where('action', 'view')->exists())->toBeTrue();
    expect($student->permissions()->where('module_name', 'result')->where('action', 'view')->exists())->toBeTrue();
    expect($student->permissions()->where('module_name', 'hall_ticket')->where('action', 'view')->exists())->toBeTrue();
    expect($student->permissions()->where('module_name', 'leave_application')->where('action', 'create')->exists())->toBeTrue();
    expect($student->permissions()->where('module_name', 'result_card')->where('action', 'view')->exists())->toBeTrue();
    expect($student->permissions()->where('module_name', 'fee_receipt_report')->where('action', 'view')->exists())->toBeTrue();
    expect($student->permissions()->where('module_name', 'hall_ticket_report')->where('action', 'view')->exists())->toBeTrue();
    expect($student->permissions()->where('module_name', 'student')->where('action', 'view')->exists())->toBeFalse();
    expect(UserRole::where('role_name', 'Demo Role')->exists())->toBeFalse();
});

test('role permission seeder preserves custom roles', function () {
    $this->seed(RolePermissionSeeder::class);

    UserRole::create([
        'role_name' => 'Demo Role',
        'description' => 'Custom role',
        'is_active' => true,
    ]);

    $this->seed(RolePermissionSeeder::class);

    expect(UserRole::where('role_name', 'Demo Role')->exists())->toBeTrue();
});

test('permission gates resolve super admin role mappings', function () {
    $this->seed(RolePermissionSeeder::class);

    $superAdminRole = UserRole::where('role_name', 'Super Admin')->first();
    $superAdminUser = User::factory()->create(['role_id' => $superAdminRole->role_id]);

    expect(Gate::forUser($superAdminUser)->allows('student.view'))->toBeTrue();
    expect(Gate::forUser($superAdminUser)->allows('fee.view'))->toBeTrue();
    expect(Gate::forUser($superAdminUser)->allows('notice.create'))->toBeTrue();
    expect(Gate::forUser($superAdminUser)->allows('exam.create'))->toBeTrue();
    expect(Gate::forUser($superAdminUser)->allows('user_permission.update'))->toBeTrue();
});

test('user permission updater exposes every page wise permission module', function () {
    $this->seed(RolePermissionSeeder::class);

    $superAdmin = UserRole::where('role_name', 'Super Admin')->firstOrFail();
    $user = User::factory()->create(['role_id' => $superAdmin->role_id]);
    $request = Request::create('/users/create');
    $request->setUserResolver(fn () => $user);

    $controller = new UserController(
        app(AccessScopeService::class),
        app(DataIntegrityService::class),
        app(\App\Services\PermissionAuditService::class),
        app(\App\Services\ApprovalWorkflowService::class)
    );
    $method = new ReflectionMethod($controller, 'permissionSections');
    $method->setAccessible(true);
    $sections = $method->invoke($controller, $request);
    $availableModules = collect($sections)->flatMap(fn (array $modules) => array_keys($modules))->values();

    $requiredModules = [
        'dashboard',
        'profile',
        'password_change',
        'chatbot',
        'university',
        'college',
        'department',
        'user',
        'user_permission',
        'role',
        'staff',
        'student',
        'category',
        'academic_year',
        'programme',
        'semester',
        'subject',
        'curriculum',
        'elective_group',
        'staff_assignment',
        'timetable_slot',
        'lecture',
        'attendance_summary',
        'attendance_defaulter',
        'exam',
        'exam_subject',
        'grade',
        'marks_entry',
        'result',
        'backlog',
        'promotion',
        'hall_ticket_config',
        'hall_ticket',
        'exam_room',
        'seating',
        'invigilator',
        'practical_schedule',
        'practical_batch',
        'practical_mark',
        'fee_category',
        'fee_structure',
        'student_ledger',
        'fee_collection',
        'receipt',
        'concession',
        'scholarship',
        'fee_report',
        'leave_type',
        'leave_balance',
        'leave_application',
        'leave_approval',
        'leave_cancellation',
        'leave_substitute',
        'holiday',
        'notice_category',
        'notice',
        'notice_audience',
        'notice_attachment',
        'notice_acknowledgement',
        'student_report',
        'attendance_report',
        'result_card',
        'fee_receipt_report',
        'hall_ticket_report',
        'staff_report',
        'activity_log',
        'certificate',
        'approval_request',
        'license_plan',
        'system_settings',
        'system_health',
    ];

    expect($availableModules)->toContain(...$requiredModules);
    expect($sections)->not->toHaveKey('Compatibility');
});
