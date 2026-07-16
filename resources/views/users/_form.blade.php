@php
    $user = $user ?? null;
    $selectedRoleId = old('role_id', $user?->role_id);
    $selectedRole = $roles->firstWhere('role_id', (int) $selectedRoleId);
    $selectedRoleName = $selectedRole?->role_name;
    $selectedPermissions = collect(old('permissions', $selectedPermissions ?? []))->map(fn ($id) => (int) $id)->all();
    $rolePermissionMap = collect($rolePermissionMap ?? [])->map(fn ($ids) => collect($ids)->map(fn ($id) => (int) $id)->values())->all();
    $canUpdateUserPermissions = $canUpdateUserPermissions ?? hasPermission('user_permission.update');
    $actionLabels = [
        'view' => 'View Page',
        'create' => 'Create',
        'update' => 'Update',
        'delete' => 'Delete',
        'approve' => 'Approve',
        'ask' => 'Ask',
        'teach' => 'Teach',
        'generate' => 'Generate / Print',
    ];
    $moduleActionLabels = [
        'staff_assignment' => [
            'view' => 'View Page',
            'create' => 'Assign Subject',
            'update' => 'Update Assignment',
            'delete' => 'Delete Assignment',
            'approve' => 'Approve Assignment',
        ],
        'user_permission' => [
            'view' => 'View Permissions',
            'update' => 'Update Permissions',
        ],
        'approval_request' => [
            'view' => 'View Requests',
            'approve' => 'Approve / Reject',
        ],
        'certificate' => [
            'view' => 'View Certificates',
            'generate' => 'Generate / Print',
        ],
    ];
    $moduleLabels = [
        'dashboard' => 'Dashboard',
        'profile' => 'Profile',
        'password_change' => 'Change Password',
        'chatbot' => 'Chatbot',
        'university' => 'Universities',
        'college' => 'Colleges',
        'department' => 'Departments',
        'user' => 'Users',
        'user_permission' => 'User Permission Updater',
        'role' => 'Roles',
        'staff' => 'Staff',
        'student' => 'Students',
        'category' => 'People Categories',
        'academic_year' => 'Academic Years',
        'programme' => 'Programmes',
        'semester' => 'Semesters',
        'subject' => 'Subjects',
        'curriculum' => 'Curriculum',
        'elective_group' => 'Elective Groups',
        'staff_assignment' => 'Subject Assignments',
        'timetable_slot' => 'Timetable Slots',
        'lecture' => 'Lectures',
        'attendance_summary' => 'Attendance Summary',
        'attendance_defaulter' => 'Defaulters',
        'exam' => 'Exams',
        'exam_subject' => 'Exam Subjects',
        'grade' => 'Grade Master',
        'marks_entry' => 'Marks Entry',
        'result' => 'Results',
        'backlog' => 'Backlogs',
        'promotion' => 'Promotions',
        'hall_ticket_config' => 'Hall Ticket Config',
        'hall_ticket' => 'Hall Tickets',
        'exam_room' => 'Exam Rooms',
        'seating' => 'Seating',
        'invigilator' => 'Invigilators',
        'practical_schedule' => 'Practical Schedule',
        'practical_batch' => 'Practical Batches',
        'practical_mark' => 'Practical Marks',
        'fee_category' => 'Fee Categories',
        'fee_structure' => 'Fee Structures',
        'student_ledger' => 'Student Ledgers',
        'fee_collection' => 'Fee Collection',
        'receipt' => 'Receipts',
        'concession' => 'Concessions',
        'scholarship' => 'Scholarships',
        'fee_report' => 'Fee Reports',
        'leave_type' => 'Leave Types',
        'leave_balance' => 'Leave Balances',
        'leave_application' => 'Applications',
        'leave_approval' => 'Approvals',
        'leave_cancellation' => 'Cancellations',
        'leave_substitute' => 'Substitutes',
        'holiday' => 'Holiday Calendar',
        'notice_category' => 'Notice Categories',
        'notice' => 'Notices',
        'notice_audience' => 'Audience',
        'notice_attachment' => 'Attachments',
        'notice_acknowledgement' => 'Acknowledgements',
        'student_report' => 'Student Reports',
        'attendance_report' => 'Attendance Reports',
        'result_card' => 'Result Cards',
        'fee_receipt_report' => 'Fee Receipts',
        'hall_ticket_report' => 'Hall Ticket PDF',
        'staff_report' => 'Staff Reports',
        'activity_log' => 'Activity Logs',
        'approval_request' => 'Approval Requests',
        'license_plan' => 'Manage Plans',
        'system_settings' => 'System Settings',
        'system_health' => 'System Health',
    ];
    $moduleKeywords = [
        'role' => 'permission permissions access role roles',
        'user_permission' => 'user permission user permissions permission updater specific user access',
        'dashboard' => 'home dashboard overview',
        'staff_assignment' => 'subject assignments teaching staff subject assignment assign faculty subject staff assignments',
        'category' => 'category categories people categories student category staff category',
        'notice_category' => 'category categories notice categories notice category',
        'fee_category' => 'category categories fee categories fee category',
        'student_report' => 'student report student reports reports',
        'attendance_report' => 'attendance report attendance reports reports',
        'result_card' => 'result report result card result cards reports',
        'fee_receipt_report' => 'fee receipt report fee receipts reports',
        'hall_ticket_report' => 'hall ticket report hall ticket pdf reports',
        'staff_report' => 'staff report staff reports reports',
        'approval_request' => 'approval approvals approval requests approve reject workflow',
        'license_plan' => 'plans subscription license licensing feature lock pricing clients',
        'system_settings' => 'settings setting system setting system settings',
        'system_health' => 'system health health',
    ];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="username" value="Username" />
        <x-text-input id="username" name="username" class="block mt-1 w-full" :value="old('username', $user?->username)" required />
        <x-input-error :messages="$errors->get('username')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email', $user?->email)" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="role_id" value="Role" />
        <select id="role_id" name="role_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
            <option value="">No role - direct permissions</option>
            @foreach ($roles as $role)
                <option value="{{ $role->role_id }}" @selected((string) old('role_id', $user?->role_id) === (string) $role->role_id)>{{ $role->role_name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('role_id')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="university_id" value="University" />
        <select id="university_id" name="university_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" @required($selectedRoleName === 'Admin')>
            <option value="">No University</option>
            @foreach ($universities as $university)
                <option value="{{ $university->university_id }}" @selected((string) old('university_id', $user?->university_id) === (string) $university->university_id)>{{ $university->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('university_id')" class="mt-2" />
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
        <x-input-label for="college_id" value="College" />
        <select id="college_id" name="college_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
            <option value="">No College</option>
            @foreach ($colleges as $college)
                <option value="{{ $college->college_id }}" @selected((string) old('college_id', $user?->college_id) === (string) $college->college_id)>{{ $college->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('college_id')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="dept_id" value="Department" />
        <select id="dept_id" name="dept_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
            <option value="">No Department</option>
            @foreach ($departments as $department)
                <option value="{{ $department->dept_id }}" @selected((string) old('dept_id', $user?->dept_id) === (string) $department->dept_id)>
                    {{ $department->name }}{{ $department->college ? ' - '.$department->college->name : '' }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('dept_id')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="programme_id" value="Programme" />
        <select id="programme_id" name="programme_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
            <option value="">No Programme</option>
            @foreach ($programmes as $programme)
                <option value="{{ $programme->programme_id }}" @selected((string) old('programme_id', $user?->programme_id) === (string) $programme->programme_id)>
                    {{ $programme->name }}{{ $programme->department ? ' - '.$programme->department->name : '' }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('programme_id')" class="mt-2" />
    </div>
</div>

<div>
    <x-input-label for="phone" value="Phone" />
    <x-text-input id="phone" name="phone" class="block mt-1 w-full" :value="old('phone', $user?->phone)" inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10" placeholder="10 digit phone number" />
    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="password" :value="$user ? 'New Password' : 'Password'" />
        <x-text-input id="password" name="password" type="password" class="block mt-1 w-full" :required="! $user" autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="password_confirmation" value="Confirm Password" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="block mt-1 w-full" :required="! $user" autocomplete="new-password" />
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
    <label class="inline-flex items-center">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_active', $user?->is_active ?? true))>
        <span class="ms-2 text-sm text-gray-700">Active</span>
    </label>
    <label class="inline-flex items-center">
        <input type="hidden" name="is_verified" value="0">
        <input type="checkbox" name="is_verified" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_verified', $user?->is_verified ?? true))>
        <span class="ms-2 text-sm text-gray-700">Verified</span>
    </label>
    <label class="inline-flex items-center">
        <input type="hidden" name="must_change_password" value="0">
        <input type="checkbox" name="must_change_password" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('must_change_password', $user?->must_change_password ?? true))>
        <span class="ms-2 text-sm text-gray-700">Force Password Change</span>
    </label>
</div>

@if($canUpdateUserPermissions)
<div class="border-t border-slate-200 pt-5" data-permission-updater>
    <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-sm font-bold text-slate-900">User Permission Updater</h3>
            <p class="mt-1 text-xs text-slate-500">Assign only the page and action permissions already available on your account.</p>
            <p class="mt-1 text-xs font-semibold text-cyan-700"><span data-permission-count>0</span> permissions selected.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" data-apply-role-permissions class="rounded-md bg-cyan-700 px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-cyan-800">Apply Selected Role Defaults</button>
            <button type="button" data-clear-permissions class="rounded-md bg-white px-3 py-2 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50">Clear</button>
            <label class="inline-flex items-center rounded-md bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700">
                <input id="select_all_permissions" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="ms-2">Select All</span>
            </label>
        </div>
    </div>

    <div class="mb-4">
        <x-input-label for="permission_search" value="Search Page Permissions" />
        <input
            id="permission_search"
            type="search"
            data-permission-search
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600"
            placeholder="Search Permissions, Categories, Student Reports, System Settings..."
        >
    </div>

    <div class="space-y-4">
        @foreach ($permissionSections as $section => $modules)
            @php($sectionSelected = collect($modules)->flatten(1)->contains(fn ($permission) => in_array((int) $permission->permission_id, $selectedPermissions, true)))
            <details class="rounded-lg border border-slate-200 bg-white shadow-sm" data-permission-section {{ $loop->first || $sectionSelected ? 'open' : '' }}>
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 rounded-lg bg-slate-50 px-4 py-3">
                    <span>
                        <span class="text-sm font-bold text-slate-900">{{ $section }}</span>
                        <span class="ml-2 text-xs font-semibold text-slate-500" data-section-count>0 selected</span>
                    </span>
                    <label class="inline-flex items-center text-xs font-bold text-slate-600" onclick="event.stopPropagation()">
                        <input type="checkbox" data-section-checkbox class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ms-2">All in section</span>
                    </label>
                </summary>

                <div class="grid gap-3 p-4 lg:grid-cols-2">
                    @foreach ($modules as $module => $permissions)
                        @php($moduleLabel = $moduleLabels[$module] ?? str($module)->replace('_', ' ')->title())
                        @php($moduleSearchLabel = str($moduleLabel.' '.$module.' '.($moduleKeywords[$module] ?? ''))->replace('_', ' ')->lower())
                        <div class="rounded-lg border border-slate-200 p-3" data-permission-module data-module-label="{{ $moduleSearchLabel }}">
                            <div class="mb-3 flex items-center justify-between gap-2">
                                <div>
                                    <div class="text-sm font-bold text-slate-900">{{ $moduleLabel }}</div>
                                    <div class="text-[11px] font-semibold text-slate-400">{{ $module }}</div>
                                </div>
                                <label class="inline-flex items-center text-xs font-bold text-slate-600">
                                    <input type="checkbox" data-module-checkbox class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ms-2">All</span>
                                </label>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($permissions as $permission)
                                    <label class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                                        <input
                                            type="checkbox"
                                            name="permissions[]"
                                            value="{{ $permission->permission_id }}"
                                            data-user-permission-checkbox
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            @checked(in_array((int) $permission->permission_id, $selectedPermissions, true))
                                        >
                                        <span>{{ $moduleActionLabels[$module][$permission->action] ?? $actionLabels[$permission->action] ?? str($permission->action)->title() }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </details>
        @endforeach
    </div>
    <x-input-error :messages="$errors->get('permissions')" class="mt-2" />
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const updater = document.querySelector('[data-permission-updater]');
        const selectAll = document.getElementById('select_all_permissions');
        const permissionCheckboxes = Array.from(document.querySelectorAll('[data-user-permission-checkbox]'));
        const roleSelect = document.getElementById('role_id');
        const applyRoleButton = document.querySelector('[data-apply-role-permissions]');
        const clearButton = document.querySelector('[data-clear-permissions]');
        const selectedCount = document.querySelector('[data-permission-count]');
        const permissionSearch = document.querySelector('[data-permission-search]');
        const rolePermissionMap = @json($rolePermissionMap);

        if (! updater || ! selectAll || permissionCheckboxes.length === 0) {
            return;
        }

        const refreshGroupCheckbox = (container, selector, checkbox) => {
            const checkboxes = Array.from(container.querySelectorAll(selector));
            const checkedCount = checkboxes.filter((item) => item.checked).length;
            checkbox.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
            checkbox.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
        };

        const refreshSelectAll = () => {
            const checkedCount = permissionCheckboxes.filter((checkbox) => checkbox.checked).length;
            selectAll.checked = checkedCount === permissionCheckboxes.length;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < permissionCheckboxes.length;

            if (selectedCount) {
                selectedCount.textContent = checkedCount;
            }

            updater.querySelectorAll('details').forEach((section) => {
                const sectionCheckbox = section.querySelector('[data-section-checkbox]');
                const sectionCount = section.querySelector('[data-section-count]');
                const sectionPermissions = Array.from(section.querySelectorAll('[data-user-permission-checkbox]'));
                const checkedInSection = sectionPermissions.filter((checkbox) => checkbox.checked).length;

                if (sectionCheckbox) {
                    refreshGroupCheckbox(section, '[data-user-permission-checkbox]', sectionCheckbox);
                }

                if (sectionCount) {
                    sectionCount.textContent = `${checkedInSection} selected`;
                }

                section.querySelectorAll('[data-module-checkbox]').forEach((moduleCheckbox) => {
                    const module = moduleCheckbox.closest('.rounded-lg.border');
                    if (module) {
                        refreshGroupCheckbox(module, '[data-user-permission-checkbox]', moduleCheckbox);
                    }
                });
            });
        };

        selectAll.addEventListener('change', () => {
            permissionCheckboxes.forEach((checkbox) => {
                checkbox.checked = selectAll.checked;
            });
            refreshSelectAll();
        });

        permissionCheckboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', refreshSelectAll);
        });

        updater.querySelectorAll('[data-section-checkbox]').forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                const section = checkbox.closest('details');
                section?.querySelectorAll('[data-user-permission-checkbox]').forEach((permission) => {
                    permission.checked = checkbox.checked;
                });
                refreshSelectAll();
            });
        });

        updater.querySelectorAll('[data-module-checkbox]').forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                const module = checkbox.closest('.rounded-lg.border');
                module?.querySelectorAll('[data-user-permission-checkbox]').forEach((permission) => {
                    permission.checked = checkbox.checked;
                });
                refreshSelectAll();
            });
        });

        applyRoleButton?.addEventListener('click', () => {
            const roleId = roleSelect?.value;
            const permissionIds = new Set((rolePermissionMap[roleId] || []).map((id) => String(id)));

            permissionCheckboxes.forEach((checkbox) => {
                checkbox.checked = permissionIds.has(checkbox.value);
            });
            refreshSelectAll();
        });

        clearButton?.addEventListener('click', () => {
            permissionCheckboxes.forEach((checkbox) => {
                checkbox.checked = false;
            });
            refreshSelectAll();
        });

        permissionSearch?.addEventListener('input', () => {
            const term = permissionSearch.value.trim().toLowerCase();

            updater.querySelectorAll('[data-permission-section]').forEach((section) => {
                let visibleModules = 0;

                section.querySelectorAll('[data-permission-module]').forEach((module) => {
                    const haystack = module.dataset.moduleLabel || '';
                    const visible = term === '' || haystack.includes(term);
                    module.classList.toggle('hidden', ! visible);

                    if (visible) {
                        visibleModules += 1;
                    }
                });

                section.classList.toggle('hidden', visibleModules === 0);
                if (term !== '' && visibleModules > 0) {
                    section.open = true;
                }
            });
        });

        refreshSelectAll();
    });
</script>
@else
    <div class="rounded-md border border-amber-200 bg-amber-50 p-3 text-sm font-semibold text-amber-900">
        This account will use selected role defaults. Grant User Permission Updater access to assign page-wise permissions manually.
    </div>
@endif
