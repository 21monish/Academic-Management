<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\Department;
use App\Models\Permission;
use App\Models\Programme;
use App\Models\University;
use App\Models\User;
use App\Models\UserRole;
use App\Services\AccessScopeService;
use App\Services\ApprovalWorkflowService;
use App\Services\DataIntegrityService;
use App\Services\PermissionAuditService;
use App\Support\ValidationRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        protected AccessScopeService $accessScope,
        protected DataIntegrityService $integrity,
        protected PermissionAuditService $permissionAudit,
        protected ApprovalWorkflowService $approvalWorkflow
    )
    {
    }

    public function index(Request $request): View
    {
        $users = $this->accessScope->applyToUsers(
            User::with('role', 'university', 'college', 'department', 'programme')->withCount('permissions'),
            $request->user()
        )
            ->when($request->string('search')->toString(), function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->integer('role_id'), fn ($query, int $roleId) => $query->where('role_id', $roleId))
            ->when($request->has('status') && $request->status !== '', fn ($query) => $query->where('is_active', (bool) $request->integer('status')))
            ->orderBy('username')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => $this->accessScope->applyToRoles(UserRole::query(), $request->user())->orderBy('role_name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('users.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUser($request);

        $validated['password_hash'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_verified'] = $request->boolean('is_verified', true);
        $validated['must_change_password'] = $request->boolean('must_change_password', true);

        unset($validated['password']);

        $permissionIds = $this->permissionIdsForWrite($validated, $request);
        $roleId = filled($validated['role_id'] ?? null) ? (int) $validated['role_id'] : null;
        unset($validated['permissions']);

        $user = User::create($validated);
        $finalPermissionIds = $permissionIds ?? $this->defaultPermissionIdsForRole($roleId, $request);
        $user->permissions()->sync($finalPermissionIds);
        $this->permissionAudit->recordSync($user, [], $finalPermissionIds, $request->user(), 'user_create');

        return redirect()->route('users.index')->with('status', 'User '.$user->username.' created successfully.');
    }

    public function edit(User $user): View|RedirectResponse
    {
        abort_unless($this->accessScope->applyToUsers(User::whereKey($user->user_id), request()->user())->exists(), 403);

        if ($redirect = $this->linkedProfileRedirect($user)) {
            return $redirect;
        }

        return view('users.edit', $this->formData(['user' => $user]));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($this->accessScope->applyToUsers(User::whereKey($user->user_id), $request->user())->exists(), 403);

        $this->abortIfLinkedProfileAccount($user);

        $validated = $this->validateUser($request, $user);

        if (! empty($validated['password'])) {
            $validated['password_hash'] = Hash::make($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_verified'] = $request->boolean('is_verified');
        $validated['must_change_password'] = $request->boolean('must_change_password');

        unset($validated['password']);

        $permissionIds = $this->permissionIdsForWrite($validated, $request);
        unset($validated['permissions']);

        $this->integrity->protectSelfAccountUpdate($user, $request, $permissionIds);

        $user->update($validated);
        if ($permissionIds !== null) {
            $this->syncDelegatedPermissions($user, $permissionIds, $request, 'user_update');
        }

        return redirect()->route('users.index')->with('status', 'User '.$user->username.' updated successfully.');
    }

    public function editPermissions(User $user): View
    {
        abort_unless($this->accessScope->applyToUsers(User::whereKey($user->user_id), request()->user())->exists(), 403);

        return view('users.permissions', $this->formData(['user' => $user]));
    }

    public function updatePermissions(Request $request, User $user): RedirectResponse
    {
        abort_unless($this->accessScope->applyToUsers(User::whereKey($user->user_id), $request->user())->exists(), 403);

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,permission_id'],
        ]);

        $permissionIds = $this->validatedDelegatedPermissionIds($validated['permissions'] ?? [], $request);
        $this->integrity->protectSelfAccountUpdate($user, $request, $permissionIds);

        $this->syncDelegatedPermissions($user, $permissionIds, $request, 'permission_update');

        return redirect()->route('users.permissions.edit', $user)->with('status', 'User '.$user->username.' permissions updated successfully.');
    }

    public function activate(User $user): RedirectResponse
    {
        abort_unless($this->accessScope->applyToUsers(User::whereKey($user->user_id), request()->user())->exists(), 403);

        $this->abortIfLinkedProfileAccount($user);

        $user->update(['is_active' => true]);

        return back()->with('status', 'User '.$user->username.' activated successfully.');
    }

    public function deactivate(Request $request, User $user): RedirectResponse
    {
        abort_unless($this->accessScope->applyToUsers(User::whereKey($user->user_id), $request->user())->exists(), 403);

        $this->abortIfLinkedProfileAccount($user);

        if ($request->user()->is($user)) {
            return back()->withErrors(['user' => 'You cannot deactivate your own account.']);
        }

        $user->update(['is_active' => false]);

        return back()->with('status', 'User '.$user->username.' deactivated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($this->accessScope->applyToUsers(User::whereKey($user->user_id), $request->user())->exists(), 403);

        $this->abortIfLinkedProfileAccount($user);

        if ($request->user()->is($user)) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $username = $user->username;

        if ($this->approvalWorkflow->requiresApproval($request->user())) {
            $this->approvalWorkflow->request(
                $request->user(),
                ApprovalWorkflowService::DELETE_USER,
                $user,
                ['username' => $username, 'email' => $user->email]
            );

            return redirect()->route('users.index')->with('status', 'User '.$username.' delete request sent for approval.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('status', 'User '.$username.' deleted successfully.');
    }

    private function formData(array $extra = []): array
    {
        $user = $extra['user'] ?? null;
        $user?->loadMissing('permissions');
        $hasRolePermissionsTable = Schema::hasTable('role_permissions');
        $assignablePermissionIds = $this->assignablePermissionIds(request());

        $roles = $this->assignableRolesQuery(
            UserRole::where('is_active', true)
                ->whereNotIn('role_name', $this->profileManagedRoleNames())
                ->when(Schema::hasColumn('user_roles', 'staff_type'), fn ($query) => $query->whereNull('staff_type')),
            request()->user()
        )->when($hasRolePermissionsTable, fn ($query) => $query->with('permissions'))
            ->orderBy('role_name')
            ->get();

        return $extra + [
            'roles' => $roles,
            'universities' => $this->accessScope->applyToUniversities(University::query(), request()->user())->orderBy('name')->get(),
            'colleges' => $this->accessScope->applyToColleges(College::where('is_active', true), request()->user())->orderBy('name')->get(),
            'departments' => $this->accessScope->applyToDepartments(Department::where('is_active', true)->with('college'), request()->user())->orderBy('name')->get(),
            'programmes' => $this->accessScope->applyToProgrammes(Programme::where('is_active', true)->with('department.college'), request()->user())->orderBy('name')->get(),
            'permissionSections' => $this->permissionSections(request()),
            'canUpdateUserPermissions' => hasPermission('user_permission.update'),
            'rolePermissionMap' => $roles->mapWithKeys(fn (UserRole $role) => [
                (string) $role->role_id => $hasRolePermissionsTable
                    ? $role->permissions
                        ->pluck('permission_id')
                        ->map(fn ($id) => (int) $id)
                        ->intersect($assignablePermissionIds)
                        ->values()
                    : collect(),
            ]),
            'selectedPermissions' => $user
                ? $user->permissions->pluck('permission_id')->map(fn ($id) => (int) $id)->all()
                : [],
        ];
    }

    private function permissionSections(Request $request): array
    {
        $assignablePermissionIds = $this->assignablePermissionIds($request);

        $permissions = Permission::query()
            ->when(
                $assignablePermissionIds === [],
                fn ($query) => $query->whereRaw('1 = 0'),
                fn ($query) => $query->whereIn('permission_id', $assignablePermissionIds)
            )
            ->orderBy('module_name')
            ->orderBy('action')
            ->get()
            ->groupBy('module_name');

        $sections = [
            'Account Pages' => ['dashboard', 'profile', 'password_change', 'chatbot'],
            'Institution' => ['university', 'college', 'department', 'user', 'user_permission', 'role'],
            'People' => ['staff', 'staff_assignment', 'student', 'category'],
            'Academic' => ['academic_year', 'programme', 'semester', 'subject', 'curriculum', 'elective_group'],
            'Attendance' => ['timetable_slot', 'lecture', 'attendance_summary', 'attendance_defaulter'],
            'Exams' => ['exam', 'exam_subject', 'grade', 'marks_entry', 'result', 'backlog', 'promotion', 'hall_ticket_config', 'hall_ticket', 'exam_room', 'seating', 'invigilator', 'practical_schedule', 'practical_batch', 'practical_mark'],
            'Fees' => ['fee_category', 'fee_structure', 'student_ledger', 'fee_collection', 'receipt', 'concession', 'scholarship', 'fee_report'],
            'Leave' => ['leave_type', 'leave_balance', 'leave_application', 'leave_approval', 'leave_cancellation', 'leave_substitute', 'holiday'],
            'Notices' => ['notice_category', 'notice', 'notice_audience', 'notice_attachment', 'notice_acknowledgement'],
            'Reports' => ['student_report', 'attendance_report', 'result_card', 'fee_receipt_report', 'hall_ticket_report', 'staff_report', 'activity_log', 'certificate'],
            'System' => ['license_plan', 'system_settings', 'system_health'],
        ];

        return collect($sections)->map(function (array $modules) use ($permissions) {
            return collect($modules)
                ->filter(fn (string $module) => $permissions->has($module))
                ->mapWithKeys(fn (string $module) => [$module => $permissions[$module]])
                ->all();
        })->filter()->all();
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        $userId = $user?->user_id;
        $role = $request->filled('role_id') ? UserRole::find($request->input('role_id')) : null;
        $roleName = $role?->role_name;

        if ($role) {
            abort_unless($this->accessScope->applyToRoles(UserRole::whereKey($role->role_id), $request->user())->exists(), 403);

            if (! $this->canAssignRole($request->user(), $role)) {
                throw ValidationException::withMessages([
                    'role_id' => 'You can only assign roles equal to or lower than your own level.',
                ]);
            }
        }

        if (in_array($roleName, $this->profileManagedRoleNames(), true) || filled($role?->staff_type)) {
            throw ValidationException::withMessages([
                'role_id' => ($roleName ?: 'Staff role').' accounts are created from their staff/student records.',
            ]);
        }

        $adminUniversityRule = $roleName === 'Admin' && ! $request->user()?->university_id
            ? 'required'
            : 'nullable';

        $validated = $request->validate([
            'role_id' => ['nullable', 'exists:user_roles,role_id'],
            'university_id' => [$adminUniversityRule, 'exists:universities,university_id'],
            'college_id' => ['nullable', 'exists:colleges,college_id'],
            'dept_id' => ['nullable', 'exists:departments,dept_id'],
            'programme_id' => ['nullable', 'exists:programmes,programme_id'],
            'username' => [
                'required',
                'string',
                'max:80',
                Rule::unique('users', 'username')->ignore($userId, 'user_id'),
            ],
            'email' => [
                ...ValidationRules::email(true, 150),
                Rule::unique('users', 'email')->ignore($userId, 'user_id'),
            ],
            'phone' => ValidationRules::phone(),
            'password' => [$user ? 'nullable' : 'required', 'confirmed', 'min:8'],
            'is_active' => ['sometimes', 'boolean'],
            'is_verified' => ['sometimes', 'boolean'],
            'must_change_password' => ['sometimes', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,permission_id'],
        ]);

        return $this->normalizeHierarchy($validated, $roleName, $request);
    }

    private function permissionIdsForWrite(array $validated, Request $request): ?array
    {
        if (! hasPermission('user_permission.update')) {
            return null;
        }

        return $this->validatedDelegatedPermissionIds($validated['permissions'] ?? [], $request);
    }

    private function validatedDelegatedPermissionIds(array $permissionIds, Request $request): array
    {
        $permissionIds = collect($permissionIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $assignablePermissionIds = $this->assignablePermissionIds($request);
        $unauthorizedPermissionIds = array_values(array_diff($permissionIds, $assignablePermissionIds));

        if ($unauthorizedPermissionIds !== []) {
            throw ValidationException::withMessages([
                'permissions' => 'You can only assign permissions that are already assigned to your account.',
            ]);
        }

        return $permissionIds;
    }

    private function defaultPermissionIdsForRole(?int $roleId, Request $request): array
    {
        if (! $roleId) {
            return [];
        }

        $rolePermissionIds = UserRole::query()
            ->whereKey($roleId)
            ->with('permissions:permissions.permission_id')
            ->first()
            ?->permissions
            ->pluck('permission_id')
            ->map(fn ($id) => (int) $id)
            ->all() ?? [];

        return array_values(array_intersect($rolePermissionIds, $this->assignablePermissionIds($request)));
    }

    private function assignablePermissionIds(Request $request): array
    {
        $user = $request->user();

        if (! $user) {
            return [];
        }

        return $user->permissions()
            ->pluck('permissions.permission_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function syncDelegatedPermissions(User $user, array $permissionIds, Request $request, string $context): void
    {
        $beforePermissionIds = $user->permissions()
            ->pluck('permissions.permission_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $assignablePermissionIds = $this->assignablePermissionIds($request);
        $preservedPermissionQuery = $user->permissions();

        if ($assignablePermissionIds !== []) {
            $preservedPermissionQuery->whereNotIn('permissions.permission_id', $assignablePermissionIds);
        }

        $preservedPermissionIds = $preservedPermissionQuery
            ->pluck('permissions.permission_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $afterPermissionIds = array_values(array_unique(array_merge($preservedPermissionIds, $permissionIds)));

        $user->permissions()->sync($afterPermissionIds);
        $this->permissionAudit->recordSync($user, $beforePermissionIds, $afterPermissionIds, $request->user(), $context);
    }

    private function normalizeHierarchy(array $validated, ?string $roleName, Request $request): array
    {
        $currentUser = $request->user();
        $currentScope = $this->accessScope->forUser($currentUser);

        if (
            $currentUser?->role?->role_name !== 'Super Admin'
            && empty($currentScope['university_id'])
            && empty($currentScope['college_id'])
            && empty($currentScope['dept_id'])
            && empty($currentScope['programme_ids'])
        ) {
            throw ValidationException::withMessages([
                'university_id' => 'You can only create users inside your assigned hierarchy.',
            ]);
        }

        if ($currentUser?->programme_id) {
            $validated['programme_id'] = $currentUser->programme_id;
        } elseif ($currentUser?->dept_id && empty($validated['programme_id'])) {
            $validated['dept_id'] = $currentUser->dept_id;
        } elseif ($currentUser?->college_id && empty($validated['dept_id']) && empty($validated['programme_id'])) {
            $validated['college_id'] = $currentUser->college_id;
        } elseif ($currentUser?->university_id && empty($validated['college_id']) && empty($validated['dept_id']) && empty($validated['programme_id'])) {
            $validated['university_id'] = $currentUser->university_id;
        }

        if (! empty($validated['programme_id'])) {
            abort_unless($this->accessScope->applyToProgrammes(Programme::whereKey($validated['programme_id']), $currentUser)->exists(), 403);
            $programme = Programme::with('department.college')->find($validated['programme_id']);
            $validated['dept_id'] = $programme?->dept_id;
            $validated['college_id'] = $programme?->department?->college_id;
            $validated['university_id'] = $programme?->department?->college?->university_id;

            return $validated;
        }

        if (! empty($validated['dept_id'])) {
            abort_unless($this->accessScope->applyToDepartments(Department::whereKey($validated['dept_id']), $currentUser)->exists(), 403);
            $department = Department::with('college')->find($validated['dept_id']);
            $validated['college_id'] = $department?->college_id;
            $validated['university_id'] = $department?->college?->university_id;
            $validated['programme_id'] = null;

            return $validated;
        }

        if (! empty($validated['college_id'])) {
            abort_unless($this->accessScope->applyToColleges(College::whereKey($validated['college_id']), $currentUser)->exists(), 403);
            $college = College::find($validated['college_id']);
            $validated['university_id'] = $college?->university_id;
            $validated['dept_id'] = null;
            $validated['programme_id'] = null;

            return $validated;
        }

        if (! empty($validated['university_id'])) {
            abort_unless($this->accessScope->applyToUniversities(University::whereKey($validated['university_id']), $currentUser)->exists(), 403);
        }

        $validated['dept_id'] = null;
        $validated['programme_id'] = null;
        $validated['college_id'] = null;

        if (($currentScope['level'] ?? null) !== 'system' && empty($validated['university_id'])) {
            throw ValidationException::withMessages([
                'university_id' => 'You can only create users inside your assigned hierarchy.',
            ]);
        }

        return $validated;
    }

    private function assignableRolesQuery($query, ?User $user)
    {
        $rank = $this->roleDelegationRankForUser($user);
        $blockedRoleNames = collect($this->roleDelegationRanks())
            ->filter(fn (int $roleRank) => $roleRank > $rank)
            ->keys()
            ->all();

        return $this->accessScope->applyToRoles($query, $user)
            ->when($blockedRoleNames !== [], fn ($roles) => $roles->whereNotIn('role_name', $blockedRoleNames));
    }

    private function canAssignRole(?User $user, UserRole $role): bool
    {
        return $this->roleDelegationRankForRole($role) <= $this->roleDelegationRankForUser($user);
    }

    private function roleDelegationRankForUser(?User $user): int
    {
        if (! $user) {
            return 0;
        }

        if ($user->role?->role_name === 'Super Admin') {
            return 100;
        }

        $roleRank = $this->roleDelegationRankForRole($user->role);
        $scopeRank = match (true) {
            filled($user->programme_id) => 30,
            filled($user->dept_id) => 40,
            filled($user->college_id) => 60,
            filled($user->university_id) => 80,
            default => $roleRank,
        };

        return min($roleRank, $scopeRank);
    }

    private function roleDelegationRankForRole(?UserRole $role): int
    {
        if (! $role) {
            return 20;
        }

        return $this->roleDelegationRanks()[$role->role_name] ?? 20;
    }

    private function roleDelegationRanks(): array
    {
        return [
            'Super Admin' => 100,
            'Admin' => 80,
            'University Admin' => 80,
            'Principal' => 60,
            'College Admin' => 60,
            'HOD' => 40,
            'Department Admin' => 40,
        ];
    }

    private function abortIfLinkedProfileAccount(User $user): void
    {
        if ($this->isLinkedProfileAccount($user)) {
            abort(422, 'This account is managed from its linked staff or student record.');
        }
    }

    private function linkedProfileRedirect(User $user): ?RedirectResponse
    {
        if (! $this->isLinkedProfileAccount($user)) {
            return null;
        }

        return match ($user->reference_type) {
            'Staff' => redirect()->route('staff.edit', $user->reference_id),
            'Student' => redirect()->route('students.edit', $user->reference_id),
            default => null,
        };
    }

    private function isLinkedProfileAccount(User $user): bool
    {
        return in_array($user->reference_type, ['Staff', 'Student'], true)
            && filled($user->reference_id);
    }

    private function profileManagedRoleNames(): array
    {
        return [
            'Student',
            'Teaching Staff',
            'Non-Teaching Staff',
            'Accountant',
        ];
    }
}
