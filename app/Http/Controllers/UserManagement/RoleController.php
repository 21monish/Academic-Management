<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\University;
use App\Models\UserRole;
use App\Services\AccessScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    private const STAFF_TYPES = ['Teaching', 'Non-Teaching', 'Both'];

    public function __construct(protected AccessScopeService $accessScope)
    {
    }

    public function index(Request $request): View
    {
        $roles = $this->accessScope->applyToRoles(UserRole::query(), $request->user())
            ->with('university')
            ->withCount('users')
            ->orderByDesc('is_system_role')
            ->orderBy('role_name')
            ->paginate(15);

        return view('roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('roles.create', $this->formData(new UserRole()));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRole($request);
        $data = [
            'role_name' => $validated['role_name'],
            'description' => $validated['description'] ?? null,
            'is_system_role' => false,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($this->hasStaffTypeColumn()) {
            $data['staff_type'] = $validated['staff_type'] ?? null;
        }

        if ($this->hasUniversityColumn()) {
            $data['university_id'] = $request->user()?->university_id ?: ($validated['university_id'] ?? null);
        }

        if ($this->hasCreatedByColumn()) {
            $data['created_by'] = $request->user()?->user_id;
        }

        UserRole::create($data);

        return redirect()->route('roles.index')->with('status', 'Role created.');
    }

    public function edit(UserRole $role): View
    {
        abort_unless($this->accessScope->applyToRoles(UserRole::whereKey($role->role_id), request()->user())->exists(), 403);

        return view('roles.edit', $this->formData($role));
    }

    public function update(Request $request, UserRole $role): RedirectResponse
    {
        abort_unless($this->accessScope->applyToRoles(UserRole::whereKey($role->role_id), $request->user())->exists(), 403);

        $validated = $this->validateRole($request, $role);
        $data = [
            'role_name' => $role->is_system_role ? $role->role_name : $validated['role_name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $role->role_name === 'Super Admin' || $request->boolean('is_active'),
        ];

        if ($this->hasStaffTypeColumn()) {
            $data['staff_type'] = $role->is_system_role ? $role->staff_type : ($validated['staff_type'] ?? null);
        }

        if ($this->hasUniversityColumn()) {
            $data['university_id'] = $role->is_system_role
                ? null
                : ($request->user()?->university_id ?: ($validated['university_id'] ?? null));
        }

        $role->update($data);

        return redirect()->route('roles.index')->with('status', 'Role updated.');
    }

    public function destroy(UserRole $role): RedirectResponse
    {
        abort_unless($this->accessScope->applyToRoles(UserRole::whereKey($role->role_id), request()->user())->exists(), 403);

        if ($role->is_system_role || $role->users()->exists()) {
            return back()->withErrors(['role' => 'This role cannot be deleted because it is protected or already assigned.']);
        }

        $role->delete();

        return redirect()->route('roles.index')->with('status', 'Role deleted.');
    }

    private function formData(UserRole $role): array
    {
        return [
            'role' => $role,
            'staffTypes' => self::STAFF_TYPES,
            'universities' => $this->accessScope->applyToUniversities(University::query(), request()->user())->orderBy('name')->get(['university_id', 'name']),
        ];
    }

    private function validateRole(Request $request, ?UserRole $role = null): array
    {
        $rules = [
            'role_name' => [
                'required',
                'string',
                'max:80',
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];

        if ($this->hasStaffTypeColumn()) {
            $rules['staff_type'] = ['nullable', Rule::in(self::STAFF_TYPES)];
        }

        if ($this->hasUniversityColumn()) {
            $universityId = $role?->is_system_role
                ? null
                : ($request->user()?->university_id ?: $request->input('university_id'));

            $rules['university_id'] = ['nullable', 'exists:universities,university_id'];
            $rules['role_name'][] = Rule::unique('user_roles', 'role_name')
                ->where(fn ($query) => $query->where('university_id', $universityId))
                ->ignore($role?->role_id, 'role_id');
        } else {
            $rules['role_name'][] = Rule::unique('user_roles', 'role_name')->ignore($role?->role_id, 'role_id');
        }

        return $request->validate($rules);
    }

    private function hasUniversityColumn(): bool
    {
        return Schema::hasColumn('user_roles', 'university_id');
    }

    private function hasCreatedByColumn(): bool
    {
        return Schema::hasColumn('user_roles', 'created_by');
    }

    private function hasStaffTypeColumn(): bool
    {
        return Schema::hasColumn('user_roles', 'staff_type');
    }
}
