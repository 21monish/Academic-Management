@php
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
        'license_plan' => 'plans subscription license licensing feature lock pricing clients',
        'system_settings' => 'settings setting system setting system settings',
        'system_health' => 'system health health',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-slate-900">Update User Permissions</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $user->username }} / {{ $user->email }}</p>
            </div>
            <a href="{{ route('users.index') }}" class="rounded-md bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">Back to Users</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <form action="{{ route('users.permissions.update', $user) }}" method="POST" class="space-y-5 rounded-lg bg-white p-6 shadow-sm">
            @csrf
            @method('PATCH')

            <div class="grid gap-4 rounded-lg border border-slate-200 bg-slate-50 p-4 md:grid-cols-4">
                <div>
                    <p class="text-xs font-bold uppercase text-slate-400">Role</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $user->role?->role_name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase text-slate-400">University</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $user->university?->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase text-slate-400">College</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $user->college?->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase text-slate-400">Department</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $user->department?->name ?? '-' }}</p>
                </div>
            </div>

            <div data-permission-updater>
                <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Page Wise Permissions</h3>
                        <p class="mt-1 text-xs text-slate-500">Select from the pages and actions already available on your account.</p>
                        @if(! $canUpdateUserPermissions)
                            <p class="mt-1 text-xs font-semibold text-amber-700">Read only: you can view this user's permissions, but cannot update them.</p>
                        @endif
                        <p class="mt-1 text-xs font-semibold text-cyan-700"><span data-permission-count>0</span> permissions selected.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" data-apply-role-permissions class="rounded-md bg-cyan-700 px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-cyan-800 disabled:cursor-not-allowed disabled:opacity-50" @disabled(! $canUpdateUserPermissions)>Apply Role Defaults</button>
                        <button type="button" data-clear-permissions class="rounded-md bg-white px-3 py-2 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-slate-200 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50" @disabled(! $canUpdateUserPermissions)>Clear</button>
                        <label class="inline-flex items-center rounded-md bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700">
                            <input id="select_all_permissions" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @disabled(! $canUpdateUserPermissions)>
                            <span class="ms-2">Select All</span>
                        </label>
                    </div>
                </div>

                <input type="hidden" id="role_id" value="{{ $user->role_id }}">

                <div class="mb-4">
                    <x-input-label for="permission_search" value="Search Page Permissions" />
                    <input id="permission_search" type="search" data-permission-search class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-600 focus:ring-cyan-600" placeholder="Search Permissions, Categories, Student Reports, System Settings...">
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
                                    <input type="checkbox" data-section-checkbox class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @disabled(! $canUpdateUserPermissions)>
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
                                                <input type="checkbox" data-module-checkbox class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @disabled(! $canUpdateUserPermissions)>
                                                <span class="ms-2">All</span>
                                            </label>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($permissions as $permission)
                                                <label class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                                                    <input type="checkbox" name="permissions[]" value="{{ $permission->permission_id }}" data-user-permission-checkbox class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(in_array((int) $permission->permission_id, $selectedPermissions, true)) @disabled(! $canUpdateUserPermissions)>
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
            </div>

            <x-input-error :messages="$errors->get('permissions')" class="mt-2" />

            <div class="flex justify-end gap-3 border-t border-slate-200 pt-5">
                <a href="{{ route('users.index') }}" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700">Cancel</a>
                @if($canUpdateUserPermissions)
                    <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Update Permissions</button>
                @endif
            </div>
        </form>
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

            if (! updater || ! selectAll || permissionCheckboxes.length === 0) return;

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
                if (selectedCount) selectedCount.textContent = checkedCount;

                updater.querySelectorAll('details').forEach((section) => {
                    const sectionCheckbox = section.querySelector('[data-section-checkbox]');
                    const sectionCount = section.querySelector('[data-section-count]');
                    const sectionPermissions = Array.from(section.querySelectorAll('[data-user-permission-checkbox]'));
                    const checkedInSection = sectionPermissions.filter((checkbox) => checkbox.checked).length;
                    if (sectionCheckbox) refreshGroupCheckbox(section, '[data-user-permission-checkbox]', sectionCheckbox);
                    if (sectionCount) sectionCount.textContent = `${checkedInSection} selected`;
                    section.querySelectorAll('[data-module-checkbox]').forEach((moduleCheckbox) => {
                        const module = moduleCheckbox.closest('[data-permission-module]');
                        if (module) refreshGroupCheckbox(module, '[data-user-permission-checkbox]', moduleCheckbox);
                    });
                });
            };

            selectAll.addEventListener('change', () => {
                permissionCheckboxes.forEach((checkbox) => checkbox.checked = selectAll.checked);
                refreshSelectAll();
            });

            permissionCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', refreshSelectAll));

            updater.querySelectorAll('[data-section-checkbox]').forEach((checkbox) => {
                checkbox.addEventListener('change', () => {
                    checkbox.closest('details')?.querySelectorAll('[data-user-permission-checkbox]').forEach((permission) => permission.checked = checkbox.checked);
                    refreshSelectAll();
                });
            });

            updater.querySelectorAll('[data-module-checkbox]').forEach((checkbox) => {
                checkbox.addEventListener('change', () => {
                    checkbox.closest('[data-permission-module]')?.querySelectorAll('[data-user-permission-checkbox]').forEach((permission) => permission.checked = checkbox.checked);
                    refreshSelectAll();
                });
            });

            applyRoleButton?.addEventListener('click', () => {
                const permissionIds = new Set((rolePermissionMap[roleSelect?.value] || []).map((id) => String(id)));
                permissionCheckboxes.forEach((checkbox) => checkbox.checked = permissionIds.has(checkbox.value));
                refreshSelectAll();
            });

            clearButton?.addEventListener('click', () => {
                permissionCheckboxes.forEach((checkbox) => checkbox.checked = false);
                refreshSelectAll();
            });

            permissionSearch?.addEventListener('input', () => {
                const term = permissionSearch.value.trim().toLowerCase();
                updater.querySelectorAll('[data-permission-section]').forEach((section) => {
                    let visibleModules = 0;
                    section.querySelectorAll('[data-permission-module]').forEach((module) => {
                        const visible = term === '' || (module.dataset.moduleLabel || '').includes(term);
                        module.classList.toggle('hidden', ! visible);
                        if (visible) visibleModules += 1;
                    });
                    section.classList.toggle('hidden', visibleModules === 0);
                    if (term !== '' && visibleModules > 0) section.open = true;
                });
            });

            refreshSelectAll();
        });
    </script>
</x-app-layout>
