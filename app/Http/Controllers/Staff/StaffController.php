<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\College;
use App\Models\Department;
use App\Models\NonTeachingStaff;
use App\Models\Staff;
use App\Models\TeachingStaff;
use App\Models\User;
use App\Models\UserRole;
use App\Services\AccessScopeService;
use App\Services\UploadService;
use App\Support\ValidationRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffController extends Controller
{
    private const STAFF_TYPES = ['Teaching', 'Non-Teaching', 'Both'];

    private const TEACHING_ROLES = [
        'Professor',
        'Associate Professor',
        'Assistant Professor',
        'Lecturer',
        'Visiting Faculty',
        'Lab Instructor',
    ];

    private const NON_TEACHING_ROLES = [
        'Peon',
        'Lab Assistant',
        'Lab Technician',
        'Librarian',
        'Accountant',
        'Clerk',
        'Office Assistant',
        'Transport Staff',
        'Security Staff',
    ];

    public function __construct(
        protected UploadService $uploads,
        protected AccessScopeService $accessScope
    )
    {
    }

    public function index(Request $request): View
    {
        $query = $this->accessScope->applyToStaff(
            Staff::query()->with(['college', 'department', 'teachingProfile', 'nonTeachingProfile']),
            $request->user()
        );

        if ($request->filled('staff_type')) {
            $query->where('staff_type', $request->string('staff_type'));
        }

        if ($request->filled('employment_type')) {
            $query->where('employment_type', $request->string('employment_type'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('college_id')) {
            $query->where('college_id', $request->string('college_id'));
        }

        $staff = $query->orderByDesc('staff_id')->paginate(15)->withQueryString();

        $colleges = $this->accessScope->applyToColleges(College::query(), $request->user())
            ->orderBy('name')
            ->get(['college_id', 'name']);

        return view('staff.index', compact('staff', 'colleges'));
    }

    public function create(): View
    {
        $colleges = $this->accessScope->applyToColleges(College::query(), request()->user())->orderBy('name')->get();
        $departments = $this->accessScope->applyToDepartments(Department::query(), request()->user())->orderBy('name')->get();
        $staffRoles = $this->staffRoleOptions(request());
        $accountRoleId = null;

        return view('staff.create', compact('colleges', 'departments', 'staffRoles', 'accountRoleId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'college_id' => ['required', 'exists:colleges,college_id'],
            'dept_id' => ['nullable', 'exists:departments,dept_id'],

            'employee_code' => ['required', 'string', 'max:30', 'unique:staff,employee_code', 'unique:users,username'],
            'first_name' => ValidationRules::shortText(true, 80),
            'last_name' => ValidationRules::shortText(true, 80),
            'gender' => ['nullable', 'in:Male,Female,Other'],
            'dob' => ['nullable', 'date'],
            'phone' => ValidationRules::phone(),
            'email' => [...ValidationRules::email(true, 150), 'unique:staff,email', 'unique:users,email'],
            'address' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            'staff_type' => ['required', Rule::in(self::STAFF_TYPES)],
            'account_role_id' => ['required', $this->staffRoleRule($request)],
            'employment_type' => ['required', 'in:Permanent,Contractual,Visiting'],
            'join_date' => ['nullable', 'date'],
            'contract_end_date' => ['nullable', 'date'],

            'is_active' => ['boolean'],

            // Teaching profile
            'qualification' => ['nullable', 'string', 'max:200'],
            'specialization' => ['nullable', 'string', 'max:200'],
            'staff_role' => ['nullable', Rule::in($this->rolesForStaffType($request->input('staff_type')))],
            'designation' => ['nullable', Rule::in(self::TEACHING_ROLES)],
            'experience_years' => ['nullable', 'integer', 'min:0'],
            'research_area' => ['nullable', 'string'],

            // Non-teaching profile
            'role' => ['nullable', Rule::in(self::NON_TEACHING_ROLES)],
            'department_section' => ['nullable', 'string', 'max:100'],
            'grade' => ['nullable', 'string', 'max:50'],
        ]);
        $validated = $this->normalizeStaffRole($validated);

        abort_unless($this->accessScope->applyToColleges(College::whereKey($validated['college_id']), $request->user())->exists(), 403);
        abort_unless($this->accessScope->applyToRoles(UserRole::whereKey($validated['account_role_id']), $request->user())->exists(), 403);
        if (! empty($validated['dept_id'])) {
            abort_unless($this->accessScope->applyToDepartments(Department::whereKey($validated['dept_id']), $request->user())->exists(), 403);
        }

        $isActive = $request->boolean('is_active', true);
        $photoUrl = $request->hasFile('photo')
            ? $this->uploads->storePublicUpload($request->file('photo'), 'uploads/photos')
            : null;

        $staff = DB::transaction(function () use ($validated, $photoUrl, $isActive): Staff {
            $staff = Staff::create([
                'college_id' => $validated['college_id'],
                'dept_id' => $validated['dept_id'] ?? null,

                'employee_code' => $validated['employee_code'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'gender' => $validated['gender'] ?? null,
                'dob' => $validated['dob'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'],
                'address' => $validated['address'] ?? null,
                'photo_url' => $photoUrl,

                'staff_type' => $validated['staff_type'],
                'employment_type' => $validated['employment_type'],
                'join_date' => $validated['join_date'] ?? null,
                'contract_end_date' => $validated['contract_end_date'] ?? null,

                'is_active' => $isActive,
            ]);

            if (in_array($staff->staff_type, ['Teaching', 'Both'], true)) {
                TeachingStaff::create([
                    'staff_id' => $staff->staff_id,
                    'qualification' => $validated['qualification'] ?? null,
                    'specialization' => $validated['specialization'] ?? null,
                    'designation' => $validated['designation'] ?? null,
                    'experience_years' => $validated['experience_years'] ?? null,
                    'research_area' => $validated['research_area'] ?? null,
                ]);
            }

            if (in_array($staff->staff_type, ['Non-Teaching', 'Both'], true)) {
                NonTeachingStaff::create([
                    'staff_id' => $staff->staff_id,
                    'role' => $validated['role'] ?? null,
                    'department_section' => $validated['department_section'] ?? null,
                    'grade' => $validated['grade'] ?? null,
                ]);
            }

            $this->syncUserAccount($staff, true, (int) $validated['account_role_id']);

            return $staff;
        });

        $initialPassword = $this->initialPassword($staff);

        return redirect()->route('staff.index')->with(
            'status',
            "Staff created. Login account created with username {$staff->employee_code} and first password {$initialPassword}. The staff member must change this password on first login."
        );
    }

    public function edit(Staff $staff): View
    {
        abort_unless($this->accessScope->applyToStaff(Staff::whereKey($staff->staff_id), request()->user())->exists(), 403);

        $colleges = $this->accessScope->applyToColleges(College::query(), request()->user())->orderBy('name')->get();
        $departments = $this->accessScope->applyToDepartments(Department::query(), request()->user())->orderBy('name')->get();

        $teaching = $staff->teachingProfile;
        $nonTeaching = $staff->nonTeachingProfile;
        $staffRoles = $this->staffRoleOptions(request());
        $accountRoleId = $staff->userAccount?->role_id;

        return view('staff.edit', compact('staff', 'colleges', 'departments', 'teaching', 'nonTeaching', 'staffRoles', 'accountRoleId'));
    }

    public function update(Request $request, Staff $staff): RedirectResponse
    {
        $linkedUserId = User::query()
            ->where('reference_type', 'Staff')
            ->where('reference_id', $staff->staff_id)
            ->value('user_id');

        $validated = $request->validate([
            'college_id' => ['required', 'exists:colleges,college_id'],
            'dept_id' => ['nullable', 'exists:departments,dept_id'],

            'employee_code' => [
                'required',
                'string',
                'max:30',
                'unique:staff,employee_code,' . $staff->staff_id . ',staff_id',
                Rule::unique('users', 'username')->ignore($linkedUserId, 'user_id'),
            ],
            'first_name' => ValidationRules::shortText(true, 80),
            'last_name' => ValidationRules::shortText(true, 80),
            'gender' => ['nullable', 'in:Male,Female,Other'],
            'dob' => ['nullable', 'date'],
            'phone' => ValidationRules::phone(),
            'email' => [
                ...ValidationRules::email(true, 150),
                'unique:staff,email,' . $staff->staff_id . ',staff_id',
                Rule::unique('users', 'email')->ignore($linkedUserId, 'user_id'),
            ],
            'address' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            'staff_type' => ['required', Rule::in(self::STAFF_TYPES)],
            'account_role_id' => ['required', $this->staffRoleRule($request)],
            'employment_type' => ['required', 'in:Permanent,Contractual,Visiting'],
            'join_date' => ['nullable', 'date'],
            'contract_end_date' => ['nullable', 'date'],

            'is_active' => ['boolean'],

            // Teaching profile
            'qualification' => ['nullable', 'string', 'max:200'],
            'specialization' => ['nullable', 'string', 'max:200'],
            'staff_role' => ['nullable', Rule::in($this->rolesForStaffType($request->input('staff_type')))],
            'designation' => ['nullable', Rule::in(self::TEACHING_ROLES)],
            'experience_years' => ['nullable', 'integer', 'min:0'],
            'research_area' => ['nullable', 'string'],

            // Non-teaching profile
            'role' => ['nullable', Rule::in(self::NON_TEACHING_ROLES)],
            'department_section' => ['nullable', 'string', 'max:100'],
            'grade' => ['nullable', 'string', 'max:50'],
        ]);
        $validated = $this->normalizeStaffRole($validated);

        abort_unless($this->accessScope->applyToStaff(Staff::whereKey($staff->staff_id), $request->user())->exists(), 403);
        abort_unless($this->accessScope->applyToColleges(College::whereKey($validated['college_id']), $request->user())->exists(), 403);
        abort_unless($this->accessScope->applyToRoles(UserRole::whereKey($validated['account_role_id']), $request->user())->exists(), 403);
        if (! empty($validated['dept_id'])) {
            abort_unless($this->accessScope->applyToDepartments(Department::whereKey($validated['dept_id']), $request->user())->exists(), 403);
        }

        $isActive = $request->boolean('is_active', true);
        $photoUrl = $request->hasFile('photo')
            ? $this->uploads->storePublicUpload($request->file('photo'), 'uploads/photos')
            : $staff->photo_url;

        DB::transaction(function () use ($staff, $validated, $photoUrl, $isActive): void {
            $staff->update([
                'college_id' => $validated['college_id'],
                'dept_id' => $validated['dept_id'] ?? null,

                'employee_code' => $validated['employee_code'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'gender' => $validated['gender'] ?? null,
                'dob' => $validated['dob'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'],
                'address' => $validated['address'] ?? null,
                'photo_url' => $photoUrl,

                'staff_type' => $validated['staff_type'],
                'employment_type' => $validated['employment_type'],
                'join_date' => $validated['join_date'] ?? null,
                'contract_end_date' => $validated['contract_end_date'] ?? null,

                'is_active' => $isActive,
            ]);

            if (in_array($staff->staff_type, ['Teaching', 'Both'], true)) {
                TeachingStaff::updateOrCreate(
                    ['staff_id' => $staff->staff_id],
                    [
                        'qualification' => $validated['qualification'] ?? null,
                        'specialization' => $validated['specialization'] ?? null,
                        'designation' => $validated['designation'] ?? null,
                        'experience_years' => $validated['experience_years'] ?? null,
                        'research_area' => $validated['research_area'] ?? null,
                    ]
                );

            } else {
                TeachingStaff::where('staff_id', $staff->staff_id)->delete();
            }

            if (in_array($staff->staff_type, ['Non-Teaching', 'Both'], true)) {
                NonTeachingStaff::updateOrCreate(
                    ['staff_id' => $staff->staff_id],
                    [
                        'role' => $validated['role'] ?? null,
                        'department_section' => $validated['department_section'] ?? null,
                        'grade' => $validated['grade'] ?? null,
                    ]
                );

            } else {
                NonTeachingStaff::where('staff_id', $staff->staff_id)->delete();
            }

            $this->syncUserAccount($staff->refresh(), false, (int) $validated['account_role_id']);
        });

        return redirect()->route('staff.index')->with('status', 'Staff updated.');
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        abort_unless($this->accessScope->applyToStaff(Staff::whereKey($staff->staff_id), request()->user())->exists(), 403);

        DB::transaction(function () use ($staff): void {
            User::query()
                ->where('reference_type', 'Staff')
                ->where('reference_id', $staff->staff_id)
                ->each(fn (User $user) => $user->delete());

            $staff->delete();
        });

        return redirect()->route('staff.index')->with('status', 'Staff deleted.');
    }

    private function syncUserAccount(Staff $staff, bool $autoCreate, ?int $accountRoleId): void
    {
        $staff->loadMissing('college', 'department', 'nonTeachingProfile');

        $hierarchy = [
            'reference_type' => 'Staff',
            'reference_id' => $staff->staff_id,
            'university_id' => $staff->college?->university_id,
            'college_id' => $staff->college_id,
            'dept_id' => $staff->dept_id,
            'programme_id' => null,
        ];

        $linkedUser = User::query()
            ->where('reference_type', 'Staff')
            ->where('reference_id', $staff->staff_id)
            ->first();

        if (! $linkedUser && ! $autoCreate) {
            return;
        }

        $userData = $hierarchy + [
            'role_id' => $accountRoleId,
            'username' => $staff->employee_code,
            'email' => $staff->email,
            'phone' => $staff->phone,
            'is_active' => (bool) $staff->is_active,
            'is_verified' => true,
        ];

        if ($linkedUser) {
            $linkedUser->update($userData);
            $linkedUser->permissions()->sync([]);

            return;
        }

        $user = User::query()->create($userData + [
            'password_hash' => Hash::make($this->initialPassword($staff)),
            'must_change_password' => true,
        ]);

        $user->permissions()->sync([]);
    }

    private function initialPassword(Staff $staff): string
    {
        return $staff->dob
            ? Carbon::parse($staff->dob)->format('dmY')
            : $staff->employee_code;
    }

    private function normalizeStaffRole(array $validated): array
    {
        $staffRole = $validated['staff_role'] ?? null;
        $staffRole = $staffRole === '' ? null : $staffRole;

        if (($validated['staff_type'] ?? null) === 'Teaching') {
            $validated['designation'] = $staffRole ?? $validated['designation'] ?? null;
            $validated['role'] = null;
        } elseif (($validated['staff_type'] ?? null) === 'Non-Teaching') {
            $validated['role'] = $staffRole ?? $validated['role'] ?? null;
            $validated['designation'] = null;
        } else {
            if (in_array($staffRole, self::TEACHING_ROLES, true)) {
                $validated['designation'] = $staffRole;
                $validated['role'] = $validated['role'] ?? null;
            } elseif (in_array($staffRole, self::NON_TEACHING_ROLES, true)) {
                $validated['role'] = $staffRole;
                $validated['designation'] = $validated['designation'] ?? null;
            }
        }

        return $validated;
    }

    private function rolesForStaffType(?string $staffType): array
    {
        return match ($staffType) {
            'Teaching' => self::TEACHING_ROLES,
            'Non-Teaching' => self::NON_TEACHING_ROLES,
            'Both' => array_values(array_unique(array_merge(self::TEACHING_ROLES, self::NON_TEACHING_ROLES))),
            default => [],
        };
    }

    private function staffRoleRule(Request $request)
    {
        $allowedTypes = $this->roleStaffTypesForStaffType($request->input('staff_type'));

        return Rule::exists('user_roles', 'role_id')
            ->where(fn ($query) => $query
                ->where('is_active', true)
                ->whereIn('staff_type', $allowedTypes));
    }

    private function staffRoleOptions(Request $request)
    {
        return $this->accessScope->applyToRoles(
            UserRole::query()
                ->where('is_active', true)
                ->whereNotNull('staff_type'),
            $request->user()
        )
            ->orderBy('staff_type')
            ->orderBy('role_name')
            ->get(['role_id', 'role_name', 'staff_type']);
    }

    private function roleStaffTypesForStaffType(?string $staffType): array
    {
        return match ($staffType) {
            'Teaching' => ['Teaching', 'Both'],
            'Non-Teaching' => ['Non-Teaching', 'Both'],
            'Both' => ['Teaching', 'Non-Teaching', 'Both'],
            default => [],
        };
    }

}

